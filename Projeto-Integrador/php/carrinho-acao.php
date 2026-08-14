<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/cart.php';

$base = base_url();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $base . '/pages/carrinho.php');
    exit();
}

if (!csrf_check($_POST['csrf'] ?? null)) {
    header('Location: ' . $base . '/pages/carrinho.php');
    exit();
}

$acao = $_POST['acao'] ?? 'add';
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$qty = isset($_POST['qty']) ? max(1, (int) $_POST['qty']) : 1;

if ($id <= 0) {
    header('Location: ' . $base . '/pages/carrinho.php');
    exit();
}

if ($acao === 'remove') {
    cart_remove($id);
} elseif ($acao === 'set') {
    cart_set($id, $qty);
} elseif ($acao === 'clear') {
    cart_clear();
} else {
    cart_add($id, $qty);
}

header('Location: ' . $base . '/pages/carrinho.php');
exit();
