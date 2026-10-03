<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
$categories = array_slice(getCategories($pdo), 0, 5);
$featured = getProducts($pdo, 'p.is_featured = 1', [], 5);
$trending = getProducts($pdo, 'p.is_trending = 1', [], 5);
$bestsellers = getProducts($pdo, 'p.is_best_seller = 1', [], 5);
$recent = getProducts($pdo, '1=1', [], 5);
?>
<section class="hero container">
  <div class="hero-card">
    <div>
      <span class="pill">Fast Delivery in 10 Minutes</span>
      <h1>Fresh Grocery, Startup Speed, Better Living</h1>
      <p>Shop fruits, vegetables, dairy, bakery and daily essentials with premium reliability.</p>
      <a class="btn" href="/grocery/shop.php">Start Shopping</a>
    </div>
    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=900&q=80" alt="Groceries" style="width:100%;border-radius:16px;object-fit:cover;max-height:320px;">
  </div>
  <div class="banner">
    <div>
      <h3>Flash Sale - Ends In <span id="flashTimer"></span></h3>
      <p>Use coupon <strong>WELCOME10</strong> on orders above Rs 299.</p>
    </div>
    <a href="/grocery/shop.php" class="btn">Explore Deals</a>
  </div>
</section>

<section class="container">
  <div class="section-title"><h2>Top Categories</h2></div>
  <div class="grid category-grid">
    <?php foreach ($categories as $cat): ?>
      <a href="/grocery/shop.php?category=<?= e($cat['slug']) ?>" class="card">
        <img src="<?= e($cat['image']) ?>" alt="<?= e($cat['name']) ?>">
        <div class="card-body"><h4><?= e($cat['name']) ?></h4><p><?= e($cat['description']) ?></p></div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php
function renderProductSection(string $title, array $products): void {
    echo '<section class="container"><div class="section-title"><h2>' . e($title) . '</h2></div><div class="grid product-grid">';
    foreach ($products as $p) {
        echo '<div class="card"><img src="' . e($p['image']) . '" alt="' . e($p['name']) . '"><div class="card-body">';
        echo '<span class="tag">' . e($p['category_name']) . '</span><h4>' . e($p['name']) . '</h4>';
        echo '<p><span class="price">Rs ' . e($p['price']) . '</span> <span class="old-price">Rs ' . e((string)$p['old_price']) . '</span></p>';
        echo '<p>Stock: ' . e((string)$p['stock']) . '</p>';
        echo '<div style="display:flex;gap:8px"><button data-product="' . e((string)$p['id']) . '" class="btn btn-sm add-to-cart-btn">Add</button><button data-product="' . e((string)$p['id']) . '" class="icon-btn wishlist-btn"><i class="fa-regular fa-heart"></i></button><a class="btn btn-sm" href="/grocery/product.php?id=' . (int)$p['id'] . '">View</a></div></div></div>';
    }
    echo '</div></section>';
}
renderProductSection('Featured Products', $featured);
renderProductSection('Trending Products', $trending);
renderProductSection('Best Sellers', $bestsellers);
renderProductSection('Recently Added', $recent);
?>
<section class="testimonial-section">
  <div class="container">
    <div class="section-title"><h2>What Customers Say</h2></div>
    <div class="testimonial-grid">
      <article class="testimonial-card">
        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=180&q=80" alt="Priya Sharma">
        <h4>Priya Sharma</h4>
        <div class="stars">★★★★★</div>
        <p>"Super quick delivery and the fruits were genuinely fresh. My weekly shopping is now sorted."</p>
      </article>
      <article class="testimonial-card">
        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=180&q=80" alt="Rahul Verma">
        <h4>Rahul Verma</h4>
        <div class="stars">★★★★★</div>
        <p>"Great prices, clean UI, and smooth checkout. Feels like a premium grocery app experience."</p>
      </article>
      <article class="testimonial-card">
        <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=180&q=80" alt="Sneha Iyer">
        <h4>Sneha Iyer</h4>
        <div class="stars">★★★★☆</div>
        <p>"The order tracking and notifications are very useful. Packaging quality was also excellent."</p>
      </article>
      <article class="testimonial-card">
        <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=180&q=80" alt="Arjun Mehta">
        <h4>Arjun Mehta</h4>
        <div class="stars">★★★★★</div>
        <p>"Best part is product variety. I found everything from staples to personal care in one place."</p>
      </article>
    </div>
  </div>
</section>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
<script>
const end = Date.now() + (6 * 60 * 60 * 1000);
setInterval(() => {
 const t = end - Date.now();
 const h = Math.max(0, Math.floor(t / 3600000));
 const m = Math.max(0, Math.floor((t % 3600000) / 60000));
 const s = Math.max(0, Math.floor((t % 60000) / 1000));
 document.getElementById('flashTimer').innerText = `${h}h ${m}m ${s}s`;
}, 1000);
</script>

