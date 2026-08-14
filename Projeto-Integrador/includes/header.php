<?php
/**
 * Cabeçalho reutilizável.
 * Variáveis opcionais antes do include:
 *   $page_title  -> título da aba
 *   $body_class  -> classe extra no <body>
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/cart.php';
$base = base_url();
$user = current_user();
$cartCount = cart_count();
$title = isset($page_title) ? $page_title . ' — TechFlow' : 'TechFlow — Peças de PC';
$bodyClass = $body_class ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TechFlow: processadores, placas de vídeo, memória, SSD e mais para montar seu PC de alta performance.">
    <meta name="theme-color" content="#120406">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap">
    <link rel="stylesheet" href="<?= e($base) ?>/assets/css/style.css">
    <title><?= e($title) ?></title>
</head>
<body class="<?= e($bodyClass) ?>" data-base="<?= e($base) ?>">
    <header class="site-header">
        <nav class="site-nav" aria-label="Navegação principal">
            <a class="logo" href="<?= e($base) ?>/index.php">
                <span class="logo-mark" aria-hidden="true">◆</span> TechFlow
            </a>
            <div class="menu">
                <a href="<?= e($base) ?>/pages/produtos.php">Produtos</a>
                <a class="cart-link" href="<?= e($base) ?>/pages/carrinho.php" aria-label="Carrinho com <?= $cartCount ?> item(ns)">
                    Carrinho
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
                <?php if ($user): ?>
                    <a href="<?= e($base) ?>/pages/dashboard.php">Meu perfil</a>
                    <a class="menu-cta" href="<?= e($base) ?>/php/logout.php">Sair</a>
                <?php else: ?>
                    <a href="<?= e($base) ?>/pages/login.php">Entrar</a>
                    <a class="menu-cta" href="<?= e($base) ?>/pages/cadastro.php">Criar conta</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main class="site-main">
