<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/data.php';

no_cache();
require_admin();

$base = base_url();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $base . '/pages/admin-produtos.php');
    exit();
}

if (!csrf_check($_POST['csrf'] ?? null)) {
    header('Location: ' . $base . '/pages/admin-produtos.php?msg=csrf');
    exit();
}

$resultado = adicionar_produto([
    'nome' => $_POST['nome'] ?? '',
    'categoria' => $_POST['categoria'] ?? '',
    'preco' => $_POST['preco'] ?? 0,
    'descricao' => $_POST['descricao'] ?? '',
    'imagem' => $_POST['imagem'] ?? 'default.png',
    'destaque' => !empty($_POST['destaque']),
], $_FILES['imagem_file'] ?? null);

header('Location: ' . $base . '/pages/admin-produtos.php?msg=' . urlencode($resultado['mensagem']));
exit();
