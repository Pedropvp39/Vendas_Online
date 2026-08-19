<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/pedidos.php';

no_cache();
require_login();

$base = base_url();
$acao = $_POST['acao'] ?? $_GET['acao'] ?? 'remover';
$pedidoId = (int) ($_POST['pedido_id'] ?? $_GET['pedido_id'] ?? 0);

if ($acao === 'remover' && $pedidoId > 0) {
    remover_pedido_usuario((int) ($_SESSION['user']['id'] ?? 0), $pedidoId);
}

header('Location: ' . $base . '/pages/dashboard.php');
exit();
