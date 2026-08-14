<?php
require_once __DIR__ . '/../includes/config.php';

$page_title = 'Carrinho';
require __DIR__ . '/../includes/header.php';
?>

<section class="cart-page">
    <div class="section-head">
        <div>
            <h1>Carrinho</h1>
            <p>Revise seus itens antes de finalizar a compra.</p>
        </div>
    </div>

    <div class="cart-layout">
        <div class="cart-panel">
            <div id="cart-items" class="cart-items" aria-live="polite"></div>
        </div>

        <aside class="summary-panel" aria-label="Resumo do pedido">
            <h3>Resumo</h3>
            <div class="summary-row">
                <span>Subtotal</span>
                <strong id="cart-subtotal">R$ 0,00</strong>
            </div>
            <div class="summary-row">
                <span>Frete</span>
                <strong id="cart-shipping">Grátis</strong>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <span id="cart-total">R$ 0,00</span>
            </div>
            <button type="button" class="btn-primary" data-cart-checkout>Finalizar compra</button>
            <button type="button" class="btn secondary" data-cart-clear>Limpar carrinho</button>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
