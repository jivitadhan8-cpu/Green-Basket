const cartToggle = document.getElementById('cartToggle');
const closeCart = document.getElementById('closeCart');
const cartSidebar = document.getElementById('cartSidebar');
const cartSidebarBody = document.getElementById('cartSidebarBody');

if (cartToggle) {
  cartToggle.addEventListener('click', () => {
    cartSidebar.classList.add('open');
    loadCartSidebar();
  });
}
if (closeCart) closeCart.addEventListener('click', () => cartSidebar.classList.remove('open'));

async function loadCartSidebar() {
  if (!cartSidebarBody) return;
  const res = await fetch('/grocery/ajax/get_cart.php');
  const data = await res.json();
  if (!data.success) {
    cartSidebarBody.innerHTML = '<p>Please login to view cart.</p>';
    return;
  }
  document.getElementById('cartCount').textContent = data.total_qty;
  cartSidebarBody.innerHTML = data.items.length ? data.items.map(item => `
    <div class="cart-item">
      <img src="${item.image}" alt="${item.name}">
      <div>
        <p>${item.name}</p>
        <small>Qty: ${item.quantity}</small>
        <p>Rs ${item.price}</p>
      </div>
    </div>`).join('') : '<p>Your cart is empty.</p>';
}

async function handleAddToCart(productId, qty = 1) {
  const res = await fetch('/grocery/ajax/add_to_cart.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `product_id=${productId}&quantity=${qty}`
  });
  const data = await res.json();
  if (data.success) {
    const cartCountEl = document.getElementById('cartCount');
    if (cartCountEl) cartCountEl.textContent = data.total_qty;
    Swal.fire({icon: 'success', title: 'Added to cart', timer: 1200, showConfirmButton: false});
  } else {
    Swal.fire({icon: 'error', title: data.message || 'Unable to add product'});
  }
}

async function handleWishlistToggle(productId) {
  const res = await fetch('/grocery/ajax/toggle_wishlist.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `product_id=${productId}`
  });
  const data = await res.json();
  Swal.fire({icon: data.success ? 'success' : 'error', title: data.message, timer: 1200, showConfirmButton: false});
}

document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
  btn.dataset.listenerAttached = '1';
  btn.addEventListener('click', () => handleAddToCart(btn.dataset.product, 1));
});

const detailAddBtn = document.querySelector('.add-to-cart-detail');
if (detailAddBtn) {
  detailAddBtn.addEventListener('click', async () => {
    const productId = detailAddBtn.dataset.product;
    const qtyInput = document.getElementById('detailQty');
    const qty = Math.max(1, parseInt(qtyInput?.value || '1', 10));
    handleAddToCart(productId, qty);
  });
}

document.querySelectorAll('.wishlist-btn').forEach(btn => {
  btn.dataset.listenerAttached = '1';
  btn.addEventListener('click', () => handleWishlistToggle(btn.dataset.product));
});

document.addEventListener('click', async (e) => {
  const addBtn = e.target.closest('.add-to-cart-btn');
  if (addBtn && !addBtn.dataset.listenerAttached) {
    e.preventDefault();
    handleAddToCart(addBtn.dataset.product, 1);
    return;
  }

  const wishBtn = e.target.closest('.wishlist-btn');
  if (wishBtn && !wishBtn.dataset.listenerAttached) {
    e.preventDefault();
    handleWishlistToggle(wishBtn.dataset.product);
  }
});

const liveSearch = document.getElementById('live-search');
const suggestionBox = document.getElementById('search-suggestions');
if (liveSearch && suggestionBox) {
  liveSearch.addEventListener('input', async () => {
    const q = liveSearch.value.trim();
    if (q.length < 2) {
      suggestionBox.style.display = 'none';
      return;
    }
    const res = await fetch(`/grocery/ajax/live_search.php?q=${encodeURIComponent(q)}`);
    const data = await res.json();
    suggestionBox.innerHTML = data.map(p => `<a href="/grocery/product.php?id=${p.id}">${p.name}</a>`).join('');
    suggestionBox.style.display = data.length ? 'block' : 'none';
  });
}

const darkModeToggle = document.getElementById('darkModeToggle');
if (localStorage.getItem('dark_mode') === '1') document.body.classList.add('dark');
if (darkModeToggle) {
  darkModeToggle.addEventListener('click', () => {
    document.body.classList.toggle('dark');
    localStorage.setItem('dark_mode', document.body.classList.contains('dark') ? '1' : '0');
  });
}

window.updateCartQty = async function(cartId, action) {
  const res = await fetch('/grocery/ajax/update_cart.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `cart_id=${cartId}&action=${action}`
  });
  const data = await res.json();
  if (data.success) location.reload();
};

async function loadUserDashboardSection(section) {
  const container = document.getElementById('userDashboardContent');
  if (!container) return;
  const res = await fetch(`/grocery/user/section.php?section=${encodeURIComponent(section)}`);
  container.innerHTML = await res.text();
}

const sidebar = document.getElementById('userDashboardSidebar');
if (sidebar) {
  const links = sidebar.querySelectorAll('[data-dashboard-section]');
  const setActive = (section) => {
    links.forEach(link => {
      const active = link.dataset.dashboardSection === section;
      link.classList.toggle('active', active);
    });
  };

  links.forEach(link => {
    link.addEventListener('click', async (e) => {
      e.preventDefault();
      const section = link.dataset.dashboardSection;
      history.replaceState(null, '', `#${section}`);
      setActive(section);
      await loadUserDashboardSection(section);
    });
  });

  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || !form.classList.contains('dashboard-ajax-form')) return;
    e.preventDefault();
    const formData = new FormData(form);
    const res = await fetch(form.action, { method: 'POST', body: formData });
    document.getElementById('userDashboardContent').innerHTML = await res.text();
    Swal.fire({icon: 'success', title: 'Profile updated', timer: 1200, showConfirmButton: false});
  });

  const initial = location.hash.replace('#', '') || 'overview';
  setActive(initial);
  loadUserDashboardSection(initial);
}
