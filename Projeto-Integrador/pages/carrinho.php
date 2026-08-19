<?php
require_once __DIR__ . '/../includes/config.php';

$page_title = 'Carrinho';
require __DIR__ . '/../includes/header.php';
?>

<section class="cart-page">
    <div class="checkout-steps" aria-label="Etapas do checkout">
        <span class="step active">Carrinho</span>
        <span class="step">Endereço</span>
        <span class="step">Entrega</span>
        <span class="step">Pagamento</span>
        <span class="step">Revisão</span>
        <span class="step">Concluir</span>
    </div>

    <div class="cart-layout">
        <div class="cart-panel">
            <div class="cart-panel-head">
                <h2>Produto e serviço</h2>
                <button type="button" class="btn-remove-all" data-cart-clear>Remover todos</button>
            </div>
            <div id="cart-items" class="cart-items" aria-live="polite"></div>
        </div>

        <aside class="summary-panel" aria-label="Resumo do pedido">
            <h3>Resumo do pedido</h3>
            <div class="summary-row">
                <span id="summary-label">Valor dos produtos (0):</span>
                <strong id="cart-subtotal">R$ 0,00</strong>
            </div>
            <div class="summary-row">
                <span>Frete:</span>
                <strong id="cart-shipping">À calcular</strong>
            </div>
            <div class="summary-total">
                <span>Total:</span>
                <span id="cart-total">R$ 0,00</span>
            </div>
            <p class="summary-note" id="summary-note">À vista no Pix: R$ 0,00</p>
            <button type="button" class="btn-primary" data-cart-checkout>Continuar</button>
            <button type="button" class="btn secondary" data-cart-back>Voltar</button>
        </aside>
    </div>
</section>

<div id="checkout-message" class="checkout-message hidden" role="dialog" aria-modal="true" aria-live="assertive">
    <div class="checkout-message-card">
        <button type="button" class="checkout-close" data-close-message aria-label="Fechar mensagem">×</button>
        <h3 id="checkout-message-title">Atenção</h3>
        <p id="checkout-message-text">O carrinho está vazio.</p>
        <button type="button" class="btn-primary btn-small" data-close-message>OK</button>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
