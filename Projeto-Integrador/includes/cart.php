<?php
/**
 * Carrinho de compras (armazenado na sessão).
 * Estrutura: $_SESSION['cart'] = [ produtoId => quantidade ].
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data.php';

const CART_MAX_QTY = 20;

function cart_raw(): array
{
    return $_SESSION['cart'] ?? [];
}

/**
 * Adiciona (ou incrementa) um produto no carrinho.
 */
function cart_add(int $id, int $qty = 1): void
{
    if (!get_produto($id) || $qty < 1) {
        return;
    }
    $cart = cart_raw();
    $novo = ($cart[$id] ?? 0) + $qty;
    $cart[$id] = max(1, min(CART_MAX_QTY, $novo));
    $_SESSION['cart'] = $cart;
}

/**
 * Define a quantidade exata de um item (0 remove).
 */
function cart_set(int $id, int $qty): void
{
    $cart = cart_raw();
    if ($qty <= 0) {
        unset($cart[$id]);
    } elseif (get_produto($id)) {
        $cart[$id] = min(CART_MAX_QTY, $qty);
    }
    $_SESSION['cart'] = $cart;
}

function cart_remove(int $id): void
{
    $cart = cart_raw();
    unset($cart[$id]);
    $_SESSION['cart'] = $cart;
}

function cart_clear(): void
{
    $_SESSION['cart'] = [];
}

/**
 * Itens do carrinho com dados do produto e subtotal.
 */
function cart_items(): array
{
    $items = [];
    foreach (cart_raw() as $id => $qty) {
        $produto = get_produto((int) $id);
        if (!$produto) {
            continue;
        }
        $produto['qty'] = (int) $qty;
        $produto['subtotal'] = $produto['preco'] * (int) $qty;
        $items[] = $produto;
    }
    return $items;
}

/**
 * Quantidade total de itens (soma das quantidades).
 */
function cart_count(): int
{
    return array_sum(cart_raw());
}

function cart_total(): float
{
    $total = 0.0;
    foreach (cart_items() as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}
