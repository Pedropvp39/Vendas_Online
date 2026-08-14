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
const money = (value) => new Intl.NumberFormat('pt-BR', {
  style: 'currency',
  currency: 'BRL'
}).format(Number(value) || 0);

function getCart() {
  try {
    const raw = JSON.parse(localStorage.getItem(CART_KEY) || '{}');
    const cart = {};
    Object.entries(raw).forEach(([key, value]) => {
      const id = Number(key);
      const qty = Number(value);
      if (Number.isFinite(id) && id > 0 && Number.isFinite(qty) && qty > 0) {
        cart[id] = Math.min(20, Math.max(1, Math.round(qty)));
      }
    });
    return cart;
  } catch (error) {
    return {};
  }
}

function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
  updateCartBadge();
}

function cartAdd(productId, quantity = 1) {
  const id = Number(productId);
  const product = TECHFLOW_PRODUCTS.find((item) => item.id === id);
  if (!product) return false;

  const cart = getCart();
  const qty = Number(quantity) || 1;
  const currentQty = Number(cart[id] || 0);
  cart[id] = Math.min(20, currentQty + qty);
  saveCart(cart);
  return true;
}

function cartSet(productId, quantity) {
  const id = Number(productId);
  const product = TECHFLOW_PRODUCTS.find((item) => item.id === id);
  if (!product) return false;

  const cart = getCart();
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
  const cart = getCart();
  delete cart[Number(productId)];
  saveCart(cart);
}

function cartClear() {
  saveCart({});
}

function cartCount() {
  return Object.values(getCart()).reduce((sum, qty) => sum + Number(qty || 0), 0);
}

function getCartItems() {
  const cart = getCart();
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

function renderCartPage() {
  const container = document.getElementById('cart-items');
  if (!container) return;

  const items = getCartItems();
  if (!items.length) {
    container.innerHTML = `
      <div class="empty-cart">
        <h3>Seu carrinho está vazio</h3>
        <p>Adicione algum produto para continuar a compra.</p>
        <a class="btn secondary" href="${document.body.dataset.base || ''}/pages/produtos.php">Ver produtos</a>
      </div>
    `;
    document.getElementById('cart-subtotal').textContent = money(0);
    document.getElementById('cart-total').textContent = money(0);
    document.getElementById('cart-shipping').textContent = 'Grátis';
    return;
  }

  const subtotal = items.reduce((sum, item) => sum + item.subtotal, 0);
  const shipping = 0;
  const total = subtotal + shipping;

  document.getElementById('cart-subtotal').textContent = money(subtotal);
  document.getElementById('cart-shipping').textContent = shipping === 0 ? 'Grátis' : money(shipping);
  document.getElementById('cart-total').textContent = money(total);

  container.innerHTML = items.map((item) => `
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
  `).join('');
}

function handleCartAction(id, action) {
  if (action === 'increase') {
    cartAdd(id, 1);
  } else if (action === 'decrease') {
    const cart = getCart();
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

function bindCartInteractions() {
  document.querySelectorAll('[data-cart-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const idInput = form.querySelector('input[name="id"]');
      const qtyInput = form.querySelector('input[name="qty"]');
      const id = Number(idInput?.value || 0);
      const qty = Number(qtyInput?.value || 1);

      if (id > 0) {
        cartAdd(id, qty);
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

  const clearButton = document.querySelector('[data-cart-clear]');
  if (clearButton) {
    clearButton.addEventListener('click', () => {
      cartClear();
      renderCartPage();
    });
  }

  const checkoutButton = document.querySelector('[data-cart-checkout]');
  if (checkoutButton) {
    checkoutButton.addEventListener('click', () => {
      alert('Pedido em desenvolvimento. Em breve você poderá finalizar a compra.');
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  updateCartBadge();
  bindCartInteractions();
  renderCartPage();
});
