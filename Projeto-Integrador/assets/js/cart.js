const TECHFLOW_PRODUCTS = [
  { id: 1, nome: 'AMD Ryzen 5 5600', categoria: 'Processadores', preco: 899.00, imagem: 'cpu-ryzen.png' },
  { id: 2, nome: 'GeForce RTX 4060', categoria: 'Placas de vídeo', preco: 2199.00, imagem: 'gpu-rtx.png' },
  { id: 3, nome: 'SSD NVMe 1TB', categoria: 'Armazenamento', preco: 349.00, imagem: 'ssd.png' },
  { id: 4, nome: 'Memória RAM DDR5 32GB', categoria: 'Memória RAM', preco: 749.00, imagem: 'ram.png' },
  { id: 5, nome: 'Placa-mãe B650 Gaming', categoria: 'Placas-mãe', preco: 1099.00, imagem: 'motherboard.png' },
  { id: 6, nome: 'Gabinete Gamer Mid-Tower', categoria: 'Gabinetes', preco: 459.00, imagem: 'gabinete.png' },
  { id: 7, nome: 'Fonte 750W 80 Plus Gold', categoria: 'Fontes', preco: 629.00, imagem: 'fonte.png' },
  { id: 8, nome: 'Water Cooler 240mm', categoria: 'Refrigeração', preco: 539.00, imagem: 'cooler.png' },
];

const CART_KEY = 'techflow_cart_v1';
const CHECKOUT_STEPS = ['Carrinho', 'Endereço', 'Entrega', 'Pagamento', 'Revisão', 'Concluir'];
const CHECKOUT_STATE = {
  step: 0,
  shipping: 'padrao',
  payment: 'cartao',
  address: {
    nome: '',
    email: '',
    telefone: '',
    cep: '',
    rua: '',
    numero: '',
    cidade: '',
    estado: ''
  }
};

const money = (value) => new Intl.NumberFormat('pt-BR', {
  style: 'currency',
  currency: 'BRL'
}).format(Number(value) || 0);

function requireLoginForPurchase() {
  const body = document.body;
  if (!body || body.dataset.loggedIn !== '1') {
    const base = body?.dataset.base || '';
    window.location.href = `${base}/pages/login.php`;
    return false;
  }
  return true;
}

function showCheckoutMessage(message, title = 'Atenção') {
  const el = document.getElementById('checkout-message');
  if (!el) return;

  const titleEl = document.getElementById('checkout-message-title');
  const textEl = document.getElementById('checkout-message-text');
  if (titleEl) titleEl.textContent = title;
  if (textEl) textEl.textContent = message;
  el.classList.remove('hidden');
}

function hideCheckoutMessage() {
  const el = document.getElementById('checkout-message');
  if (el) el.classList.add('hidden');
}

function getCart() {
  try {
    const stored = JSON.parse(localStorage.getItem(CART_KEY) || '{}');
    const cart = {};
    Object.entries(stored).forEach(([key, value]) => {
      const id = Number(key);
      const qty = Number(value);
      if (Number.isFinite(id) && id > 0 && Number.isFinite(qty) && qty > 0) {
        cart[id] = Math.min(20, Math.max(1, Math.round(qty)));
      }
    });

    const sessionCart = document.body?.dataset?.sessionCart ? JSON.parse(document.body.dataset.sessionCart) : {};
    const merged = { ...sessionCart, ...cart };

    const hasStoredItems = Object.keys(cart).length > 0;
    const hasSessionItems = Object.keys(sessionCart || {}).length > 0;
    if (!hasStoredItems && hasSessionItems) {
      localStorage.setItem(CART_KEY, JSON.stringify(merged));
    }

    return merged;
  } catch (error) {
    const sessionCart = document.body?.dataset?.sessionCart ? JSON.parse(document.body.dataset.sessionCart) : {};
    return sessionCart || {};
  }
}

function sanitizeCart() {
  const cart = getCart();
  const valid = {};
  let invalid = false;

  Object.entries(cart).forEach(([productId, qty]) => {
    const product = TECHFLOW_PRODUCTS.find((item) => item.id === Number(productId));
    if (!product) {
      invalid = true;
      return;
    }
    valid[productId] = Math.min(20, Math.max(1, Number(qty) || 1));
  });

  if (Object.keys(valid).length !== Object.keys(cart).length) {
    localStorage.setItem(CART_KEY, JSON.stringify(valid));
  }

  if (invalid) {
    if (Object.keys(valid).length === 0) {
      showCheckoutMessage('Seu carrinho está vazio. Adicione um produto para continuar a compra.');
    } else {
      showCheckoutMessage('Um ou mais itens do carrinho não existem mais e foram removidos.');
    }
  }

  return valid;
}

function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
  updateCartBadge();
}

function cartAdd(productId, quantity = 1) {
  const id = Number(productId);
  const product = TECHFLOW_PRODUCTS.find((item) => item.id === id);
  if (!product) {
    showCheckoutMessage('Este produto não existe mais ou está indisponível no momento.');
    return false;
  }

  const cart = sanitizeCart();
  const qty = Number(quantity) || 1;
  const currentQty = Number(cart[id] || 0);
  cart[id] = Math.min(20, currentQty + qty);
  saveCart(cart);
  return true;
}

function cartSet(productId, quantity) {
  const id = Number(productId);
  const product = TECHFLOW_PRODUCTS.find((item) => item.id === id);
  if (!product) {
    showCheckoutMessage('Produto inválido ou indisponível no momento.');
    return false;
  }

  const cart = sanitizeCart();
  const qty = Number(quantity) || 0;
  if (qty <= 0) {
    delete cart[id];
  } else {
    cart[id] = Math.min(20, Math.max(1, Math.round(qty)));
  }
  saveCart(cart);
  return true;
}

function cartRemove(productId) {
  const cart = sanitizeCart();
  delete cart[Number(productId)];
  saveCart(cart);
}

function cartClear() {
  saveCart({});
  CHECKOUT_STATE.step = 0;
  renderCartPage();
}

function cartCount() {
  return Object.values(sanitizeCart()).reduce((sum, qty) => sum + Number(qty || 0), 0);
}

function getCartItems() {
  const cart = sanitizeCart();
  return Object.entries(cart)
    .map(([productId, qty]) => {
      const product = TECHFLOW_PRODUCTS.find((item) => item.id === Number(productId));
      if (!product) return null;
      return {
        ...product,
        qty: Number(qty),
        subtotal: product.preco * Number(qty),
      };
    })
    .filter(Boolean);
}

function updateCartBadge() {
  const count = cartCount();
  document.querySelectorAll('.cart-badge').forEach((badge) => {
    badge.textContent = String(count);
    badge.hidden = count <= 0;
  });

  const cartLinks = document.querySelectorAll('.cart-link');
  cartLinks.forEach((link) => {
    const label = link.getAttribute('aria-label') || 'Carrinho';
    link.setAttribute('aria-label', label.replace(/\d+ item\(ns\)/, `${count} item(ns)`));
  });
}

function getShippingPrice(method) {
  return method === 'expressa' ? 19.90 : 0;
}

function getPaymentDiscount(method) {
  return method === 'pix' ? 0.10 : 0;
}

function updateCheckoutSummary(items) {
  const subtotal = items.reduce((sum, item) => sum + item.subtotal, 0);
  const shipping = getShippingPrice(CHECKOUT_STATE.shipping);
  const discount = getPaymentDiscount(CHECKOUT_STATE.payment) * subtotal;
  const total = Math.max(0, subtotal + shipping - discount);

  const subtotalEl = document.getElementById('cart-subtotal');
  const totalEl = document.getElementById('cart-total');
  const shippingEl = document.getElementById('cart-shipping');
  const summaryNoteEl = document.getElementById('summary-note');
  const summaryLabelEl = document.getElementById('summary-label');

  if (summaryLabelEl) {
    summaryLabelEl.textContent = `Valor dos produtos (${items.length}):`;
  }

  if (subtotalEl) subtotalEl.textContent = money(subtotal);
  if (totalEl) totalEl.textContent = money(total);
  if (shippingEl) shippingEl.textContent = shipping === 0 ? 'Grátis' : money(shipping);
  if (summaryNoteEl) {
    if (CHECKOUT_STATE.payment === 'pix') {
      summaryNoteEl.textContent = `À vista no Pix: ${money(total)}`;
    } else {
      summaryNoteEl.textContent = `Em até 12x sem juros: ${money(total)}`;
    }
  }

  return { subtotal, shipping, discount, total };
}

function updateCheckoutStepsUI() {
  document.querySelectorAll('.step').forEach((step, index) => {
    step.classList.toggle('active', index <= CHECKOUT_STATE.step);
  });
}

function validateAddressForm() {
  const requiredFields = ['nome', 'email', 'telefone', 'cep', 'rua', 'numero', 'cidade', 'estado'];
  for (const field of requiredFields) {
    const value = (CHECKOUT_STATE.address[field] || '').trim();
    if (!value) {
      showCheckoutMessage('Preencha todos os dados do endereço para continuar o checkout.', 'Dados incompletos');
      return false;
    }
  }
  return true;
}

function renderCartStepContent(items) {
  const container = document.getElementById('cart-items');
  if (!container) return;

  const step = CHECKOUT_STATE.step;

  if (!items.length) {
    container.innerHTML = `
      <div class="empty-cart">
        <h3>Seu carrinho está vazio</h3>
        <p>Adicione algum produto para continuar a compra.</p>
        <a class="btn secondary" href="${document.body.dataset.base || ''}/pages/produtos.php">Ver produtos</a>
      </div>
    `;
    return;
  }

  if (step === 0) {
    container.innerHTML = `
      <div class="checkout-options">
        <div class="option-group">
          <h4>Frete</h4>
          <div class="option-list">
            <div class="option-item">
              <label>
                <input type="radio" name="frete" value="padrao" ${CHECKOUT_STATE.shipping === 'padrao' ? 'checked' : ''}>
                <span><strong>Entrega padrão</strong><br><small>3 a 5 dias úteis</small></span>
              </label>
              <strong>Grátis</strong>
            </div>
            <div class="option-item">
              <label>
                <input type="radio" name="frete" value="expressa" ${CHECKOUT_STATE.shipping === 'expressa' ? 'checked' : ''}>
                <span><strong>Entrega expressa</strong><br><small>1 a 2 dias úteis</small></span>
              </label>
              <strong>R$ 19,90</strong>
            </div>
          </div>
        </div>

        <div class="option-group">
          <h4>Pagamento</h4>
          <div class="option-list">
            <div class="option-item">
              <label>
                <input type="radio" name="pagamento" value="cartao" ${CHECKOUT_STATE.payment === 'cartao' ? 'checked' : ''}>
                <span><strong>Cartão de crédito</strong><br><small>até 12x sem juros</small></span>
              </label>
            </div>
            <div class="option-item">
              <label>
                <input type="radio" name="pagamento" value="pix" ${CHECKOUT_STATE.payment === 'pix' ? 'checked' : ''}>
                <span><strong>Pix</strong><br><small>10% de desconto</small></span>
              </label>
            </div>
          </div>
        </div>
      </div>

      ${items.map((item) => `
        <article class="cart-item" data-product-id="${item.id}">
          <div class="cart-item-image">
            <img src="${document.body.dataset.base || ''}/assets/img/${item.imagem}" alt="${item.nome}">
          </div>
          <div class="cart-item-body">
            <h4>${item.nome}</h4>
            <p class="cart-item-meta">${item.categoria}</p>
            <div class="cart-item-price">${money(item.preco)}</div>
          </div>
          <div class="cart-item-actions">
            <div class="qty-control" aria-label="Controle de quantidade">
              <button type="button" data-action="decrease" data-id="${item.id}" aria-label="Diminuir quantidade">−</button>
              <span>${item.qty}</span>
              <button type="button" data-action="increase" data-id="${item.id}" aria-label="Aumentar quantidade">+</button>
            </div>
            <button type="button" class="link-like" data-action="remove" data-id="${item.id}">Remover</button>
          </div>
        </article>
      `).join('')}
    `;

    document.querySelectorAll('input[name="frete"]').forEach((input) => {
      input.addEventListener('change', (event) => {
        CHECKOUT_STATE.shipping = event.target.value;
        renderCartPage();
      });
    });

    document.querySelectorAll('input[name="pagamento"]').forEach((input) => {
      input.addEventListener('change', (event) => {
        CHECKOUT_STATE.payment = event.target.value;
        renderCartPage();
      });
    });

    return;
  }

  if (step === 1) {
    container.innerHTML = `
      <div class="checkout-step-panel">
        <div>
          <h3>Endereço de entrega</h3>
          <p>Informe os dados para onde a compra será enviada.</p>
        </div>
        <div class="checkout-form-grid">
          <div class="field-full">
            <label for="checkout-nome">Nome completo</label>
            <input id="checkout-nome" name="nome" value="${CHECKOUT_STATE.address.nome}" required>
          </div>
          <div>
            <label for="checkout-email">E-mail</label>
            <input id="checkout-email" name="email" type="email" value="${CHECKOUT_STATE.address.email}" required>
          </div>
          <div>
            <label for="checkout-telefone">Telefone</label>
            <input id="checkout-telefone" name="telefone" value="${CHECKOUT_STATE.address.telefone}" required>
          </div>
          <div>
            <label for="checkout-cep">CEP</label>
            <input id="checkout-cep" name="cep" value="${CHECKOUT_STATE.address.cep}" required>
          </div>
          <div class="field-full">
            <label for="checkout-rua">Rua</label>
            <input id="checkout-rua" name="rua" value="${CHECKOUT_STATE.address.rua}" required>
          </div>
          <div>
            <label for="checkout-numero">Número</label>
            <input id="checkout-numero" name="numero" value="${CHECKOUT_STATE.address.numero}" required>
          </div>
          <div>
            <label for="checkout-cidade">Cidade</label>
            <input id="checkout-cidade" name="cidade" value="${CHECKOUT_STATE.address.cidade}" required>
          </div>
          <div>
            <label for="checkout-estado">Estado</label>
            <input id="checkout-estado" name="estado" value="${CHECKOUT_STATE.address.estado}" required>
          </div>
        </div>
      </div>
    `;

    ['nome', 'email', 'telefone', 'cep', 'rua', 'numero', 'cidade', 'estado'].forEach((field) => {
      const input = document.getElementById(`checkout-${field}`);
      if (!input) return;
      input.addEventListener('input', (event) => {
        CHECKOUT_STATE.address[field] = event.target.value;
      });
    });
    return;
  }

  if (step === 2) {
    container.innerHTML = `
      <div class="checkout-step-panel">
        <div>
          <h3>Entrega</h3>
          <p>Escolha a opção de entrega ideal para o seu pedido.</p>
        </div>
        <div class="checkout-summary-box">
          <div><span>Entrega padrão</span><strong>Grátis</strong></div>
          <div><span>Entrega expressa</span><strong>R$ 19,90</strong></div>
        </div>
        <div class="option-list">
          <div class="option-item">
            <label>
              <input type="radio" name="frete-passos" value="padrao" ${CHECKOUT_STATE.shipping === 'padrao' ? 'checked' : ''}>
              <span><strong>Entrega padrão</strong><br><small>3 a 5 dias úteis</small></span>
            </label>
            <strong>Grátis</strong>
          </div>
          <div class="option-item">
            <label>
              <input type="radio" name="frete-passos" value="expressa" ${CHECKOUT_STATE.shipping === 'expressa' ? 'checked' : ''}>
              <span><strong>Entrega expressa</strong><br><small>1 a 2 dias úteis</small></span>
            </label>
            <strong>R$ 19,90</strong>
          </div>
        </div>
      </div>
    `;

    document.querySelectorAll('input[name="frete-passos"]').forEach((input) => {
      input.addEventListener('change', (event) => {
        CHECKOUT_STATE.shipping = event.target.value;
        renderCartPage();
      });
    });
    return;
  }

  if (step === 3) {
    container.innerHTML = `
      <div class="checkout-step-panel">
        <div>
          <h3>Pagamento</h3>
          <p>Selecione a forma de pagamento mais conveniente.</p>
        </div>
        <div class="option-list">
          <div class="option-item">
            <label>
              <input type="radio" name="pagamento-passos" value="cartao" ${CHECKOUT_STATE.payment === 'cartao' ? 'checked' : ''}>
              <span><strong>Cartão de crédito</strong><br><small>até 12x sem juros</small></span>
            </label>
          </div>
          <div class="option-item">
            <label>
              <input type="radio" name="pagamento-passos" value="pix" ${CHECKOUT_STATE.payment === 'pix' ? 'checked' : ''}>
              <span><strong>Pix</strong><br><small>10% de desconto</small></span>
            </label>
          </div>
        </div>
      </div>
    `;

    document.querySelectorAll('input[name="pagamento-passos"]').forEach((input) => {
      input.addEventListener('change', (event) => {
        CHECKOUT_STATE.payment = event.target.value;
        renderCartPage();
      });
    });
    return;
  }

  if (step === 4) {
    const { subtotal, shipping, discount, total } = updateCheckoutSummary(items);
    const nome = CHECKOUT_STATE.address.nome || 'Cliente';
    const cidade = CHECKOUT_STATE.address.cidade || 'Cidade';
    const estado = CHECKOUT_STATE.address.estado || 'Estado';

    container.innerHTML = `
      <div class="checkout-step-panel">
        <div>
          <h3>Revisão do pedido</h3>
          <p>Confirme os dados antes de finalizar a compra.</p>
        </div>
        <div class="checkout-summary-box">
          <div><span>Cliente</span><strong>${nome}</strong></div>
          <div><span>Entrega</span><strong>${cidade} - ${estado}</strong></div>
          <div><span>Frete</span><strong>${shipping === 0 ? 'Grátis' : money(shipping)}</strong></div>
          <div><span>Pagamento</span><strong>${CHECKOUT_STATE.payment === 'pix' ? 'Pix' : 'Cartão de crédito'}</strong></div>
          <div><span>Subtotal</span><strong>${money(subtotal)}</strong></div>
          <div><span>Desconto</span><strong>${money(discount)}</strong></div>
          <div><span>Total</span><strong>${money(total)}</strong></div>
        </div>
      </div>
    `;
    return;
  }

  container.innerHTML = `
    <div class="checkout-success">
      <h3>Pedido concluído</h3>
      <p>Obrigado por comprar com a TechFlow.</p>
      <p>Seu pedido foi confirmado e a confirmação foi enviada para o e-mail informado.</p>
    </div>
  `;
}

function renderCartPage() {
  const items = getCartItems();
  const checkoutButton = document.querySelector('[data-cart-checkout]');
  const backButton = document.querySelector('[data-cart-back]');

  updateCheckoutSummary(items);
  updateCheckoutStepsUI();
  renderCartStepContent(items);

  if (checkoutButton) {
    const buttonLabels = ['Continuar', 'Continuar', 'Continuar', 'Continuar', 'Finalizar compra', 'Novo pedido'];
    checkoutButton.textContent = buttonLabels[CHECKOUT_STATE.step] || 'Continuar';
    checkoutButton.disabled = false;
  }

  if (backButton) {
    if (CHECKOUT_STATE.step === 0) {
      backButton.textContent = 'Voltar';
    } else {
      backButton.textContent = 'Voltar';
    }
  }
}

function handleCartAction(id, action) {
  if (!requireLoginForPurchase()) {
    return;
  }

  if (action === 'increase') {
    cartAdd(id, 1);
  } else if (action === 'decrease') {
    const cart = sanitizeCart();
    const current = Number(cart[id] || 0);
    if (current <= 1) {
      cartRemove(id);
    } else {
      cartSet(id, current - 1);
    }
  } else if (action === 'remove') {
    cartRemove(id);
  }
  renderCartPage();
}

async function finalizeCheckout() {
  const items = getCartItems();
  if (!items.length) {
    showCheckoutMessage('Seu carrinho está vazio. Adicione pelo menos um produto antes de continuar.');
    return;
  }

  const base = document.body.dataset.base || '';
  try {
    const payload = new URLSearchParams({ cart: JSON.stringify(Object.fromEntries(items.map((item) => [String(item.id), item.qty]))) });
    const response = await fetch(`${base}/php/finalizar-pedido.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: payload.toString()
    });

    const data = await response.json();
    if (!response.ok || !data.ok) {
      throw new Error(data.mensagem || 'Não foi possível concluir a compra.');
    }

    localStorage.removeItem(CART_KEY);
    CHECKOUT_STATE.step = 5;
    window.location.href = `${base}/pages/dashboard.php`;
  } catch (error) {
    showCheckoutMessage(error.message || 'Não foi possível concluir a compra.');
  }
}

function goToNextStep() {
  if (!requireLoginForPurchase()) {
    return;
  }

  const items = getCartItems();
  if (!items.length) {
    showCheckoutMessage('Seu carrinho está vazio. Adicione pelo menos um produto antes de continuar.');
    return;
  }

  if (CHECKOUT_STATE.step === 1 && !validateAddressForm()) {
    return;
  }

  if (CHECKOUT_STATE.step === CHECKOUT_STEPS.length - 2) {
    finalizeCheckout();
    return;
  }

  if (CHECKOUT_STATE.step < CHECKOUT_STEPS.length - 1) {
    CHECKOUT_STATE.step += 1;
    renderCartPage();
  }
}

function goToPreviousStep() {
  if (CHECKOUT_STATE.step === 0) {
    const base = document.body.dataset.base || '';
    window.location.href = `${base}/pages/produtos.php`;
    return;
  }

  CHECKOUT_STATE.step = Math.max(0, CHECKOUT_STATE.step - 1);
  renderCartPage();
}

function bindCartInteractions() {
  document.querySelectorAll('[data-cart-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      if (!requireLoginForPurchase()) {
        return;
      }

      const idInput = form.querySelector('input[name="id"]');
      const qtyInput = form.querySelector('input[name="qty"]');
      const id = Number(idInput?.value || 0);
      const qty = Number(qtyInput?.value || 1);

      if (!id || id <= 0) {
        showCheckoutMessage('Produto inválido. Não foi possível adicionar este item ao carrinho.');
        return;
      }

      const added = cartAdd(id, qty);
      if (added) {
        const base = document.body.dataset.base || '';
        window.location.href = `${base}/pages/carrinho.php`;
      }
    });
  });

  document.addEventListener('click', (event) => {
    const target = event.target.closest('[data-action]');
    if (!target) return;
    const id = Number(target.dataset.id || 0);
    const action = target.dataset.action;
    handleCartAction(id, action);
  });

  const closeButtons = document.querySelectorAll('[data-close-message]');
  closeButtons.forEach((button) => {
    button.addEventListener('click', hideCheckoutMessage);
  });

  const clearButton = document.querySelector('[data-cart-clear]');
  if (clearButton) {
    clearButton.addEventListener('click', () => {
      cartClear();
    });
  }

  const backButton = document.querySelector('[data-cart-back]');
  if (backButton) {
    backButton.addEventListener('click', goToPreviousStep);
  }

  const checkoutButton = document.querySelector('[data-cart-checkout]');
  if (checkoutButton) {
    checkoutButton.addEventListener('click', () => {
      if (CHECKOUT_STATE.step === 5) {
        CHECKOUT_STATE.step = 0;
        CHECKOUT_STATE.shipping = 'padrao';
        CHECKOUT_STATE.payment = 'cartao';
        CHECKOUT_STATE.address = {
          nome: '',
          email: '',
          telefone: '',
          cep: '',
          rua: '',
          numero: '',
          cidade: '',
          estado: ''
        };
        renderCartPage();
        return;
      }

      goToNextStep();
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  updateCartBadge();
  bindCartInteractions();
  renderCartPage();
});
