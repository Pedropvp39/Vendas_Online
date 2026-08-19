<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/pedidos.php';
require_once __DIR__ . '/../includes/data.php';

no_cache();
require_login();

header('Content-Type: application/json; charset=utf-8');

$cartData = $_POST['cart'] ?? $_REQUEST['cart'] ?? '[]';
$cart = json_decode((string) $cartData, true);
if (!is_array($cart) || empty($cart)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensagem' => 'Carrinho vazio.']);
    exit();
}

$itens = [];
foreach ($cart as $id => $qty) {
    $produto = get_produto((int) $id);
    if (!$produto) {
        continue;
    }

    $itens[] = [
        'id' => $produto['id'],
        'nome' => $produto['nome'],
        'categoria' => $produto['categoria'],
        'preco' => $produto['preco'],
        'qty' => max(1, (int) $qty),
    ];
}

if (!$itens) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensagem' => 'Nenhum produto válido encontrado no carrinho.']);
    exit();
}

$resultado = registrar_pedidos_usuario((int) ($_SESSION['user']['id'] ?? 0), $itens);
if (!$resultado['ok']) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensagem' => $resultado['mensagem']]);
    exit();
}

$_SESSION['cart'] = [];
set_flash('success', 'Obrigado pela sua compra!');

echo json_encode(['ok' => true, 'mensagem' => 'Pedido concluído com sucesso.']);
