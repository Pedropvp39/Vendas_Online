<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../php/conexao.php';

function status_pedido_aleatorio(int $pedidoId, ?string $statusAtual = null): string
{
    $statusAtual = trim((string) ($statusAtual ?? ''));
    if ($statusAtual === 'Entregue') {
        return 'Entregue';
    }

    $lista = ['Pago', 'Separando', 'Em transporte', 'Entregue'];
    $valor = (int) ((time() + $pedidoId * 17) / 180) % count($lista);
    return $lista[$valor];
}

function registrar_pedidos_usuario(int $usuarioId, array $itens): array
{
    if ($usuarioId <= 0 || empty($itens)) {
        return ['ok' => false, 'mensagem' => 'Pedido vazio.'];
    }

    try {
        $db = db_connect();
        foreach ($itens as $item) {
            $nome = trim((string) ($item['nome'] ?? ''));
            $categoria = trim((string) ($item['categoria'] ?? ''));
            $preco = (float) ($item['preco'] ?? 0);
            $quantidade = max(1, (int) ($item['qty'] ?? $item['quantidade'] ?? 1));
            $produtoId = (int) ($item['id'] ?? 0);

            if ($nome === '' || $categoria === '' || $produtoId <= 0) {
                continue;
            }

            $stmt = $db->prepare('INSERT INTO pedidos (usuario_id, produto_id, produto_nome, categoria, preco, quantidade, status, removido) VALUES (?, ?, ?, ?, ?, ?, ?, 0)');
            $status = 'Pago';
            $stmt->bind_param('iissdis', $usuarioId, $produtoId, $nome, $categoria, $preco, $quantidade, $status);
            $stmt->execute();
        }

        return ['ok' => true, 'mensagem' => 'Pedido concluído com sucesso.'];
    } catch (Throwable $e) {
        error_log('registrar_pedidos_usuario: ' . $e->getMessage());
        return ['ok' => false, 'mensagem' => 'Não foi possível concluir o pedido.'];
    }
}

function get_meus_pedidos(int $usuarioId): array
{
    if ($usuarioId <= 0) {
        return [];
    }

    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT * FROM pedidos WHERE usuario_id = ? AND removido = 0 ORDER BY id DESC');
        $stmt->bind_param('i', $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();

        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedido = [
                'id' => (int) ($row['id'] ?? 0),
                'produto_id' => (int) ($row['produto_id'] ?? 0),
                'produto_nome' => (string) ($row['produto_nome'] ?? ''),
                'categoria' => (string) ($row['categoria'] ?? ''),
                'preco' => (float) ($row['preco'] ?? 0),
                'quantidade' => (int) ($row['quantidade'] ?? 1),
                'status' => status_pedido_aleatorio((int) ($row['id'] ?? 0), (string) ($row['status'] ?? '')),
                'criado_em' => (string) ($row['criado_em'] ?? ''),
            ];
            $pedidos[] = $pedido;
        }

        return $pedidos;
    } catch (Throwable $e) {
        error_log('get_meus_pedidos: ' . $e->getMessage());
        return [];
    }
}

function remover_pedido_usuario(int $usuarioId, int $pedidoId): bool
{
    if ($usuarioId <= 0 || $pedidoId <= 0) {
        return false;
    }

    try {
        $db = db_connect();
        $stmt = $db->prepare('UPDATE pedidos SET removido = 1 WHERE id = ? AND usuario_id = ?');
        $stmt->bind_param('ii', $pedidoId, $usuarioId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    } catch (Throwable $e) {
        error_log('remover_pedido_usuario: ' . $e->getMessage());
        return false;
    }
}
