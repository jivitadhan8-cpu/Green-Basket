<?php
$pageTitle = 'Product';
require_once __DIR__ . '/includes/header.php';

$productId = (int)($_GET['id'] ?? 0);
$slug = trim($_GET['slug'] ?? '');

if ($productId > 0) {
  $stmt = $pdo->prepare('SELECT p.*, c.name as category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = ? LIMIT 1');
  $stmt->execute([$productId]);
} else {
  $stmt = $pdo->prepare('SELECT p.*, c.name as category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.slug = ? LIMIT 1');
  $stmt->execute([$slug]);
}

$product = $stmt->fetch();
if (!$product) {
  echo '<div class="container"><p>Product not found.</p></div>';
  require_once __DIR__ . '/includes/footer.php';
  exit;
}

$reviewStmt = $pdo->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.product_id=? AND r.status='approved' ORDER BY r.created_at DESC");
$reviewStmt->execute([$product['id']]);
$reviews = $reviewStmt->fetchAll();

$avgRating = $reviews ? round(array_sum(array_map(fn($r) => (int)$r['rating'], $reviews)) / count($reviews), 1) : (float)$product['rating'];
$filledStars = (int)round($avgRating);
$starIcons = str_repeat('&#9733;', $filledStars) . str_repeat('&#9734;', 5 - $filledStars);
?>

<section class="container" style="padding:28px 0">
  <div class="product-layout">
    <div class="gallery-box">
      <div class="main-photo-wrap">
        <img class="main-photo" src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
      </div>
    </div>

    <div class="purchase-sticky">
      <div class="panel-card product-info-box">
        <p class="tag"><?= e($product['category_name']) ?></p>
        <h1><?= e($product['name']) ?></h1>
        <div class="rating-line"><span class="stars-gold"><?= $starIcons ?></span> <strong><?= e((string)$avgRating) ?></strong>/5</div>
        <p class="price-lg">Rs <?= number_format((float)$product['price'], 2) ?>
          <?php if (!empty($product['old_price'])): ?><span class="old-price">Rs <?= number_format((float)$product['old_price'], 2) ?></span><?php endif; ?>
        </p>
        <p><strong>Stock:</strong> <?= (int)$product['stock'] ?> <?= (int)$product['stock'] < 20 ? '<span style="color:#dc2626">(Low stock)</span>' : '' ?></p>
      </div>

      <div class="panel-card action-box">
        <h3>Quick Actions</h3>
        <div class="qty-wrap">
          <label for="detailQty">Quantity</label>
          <input id="detailQty" type="number" min="1" max="<?= max(1, (int)$product['stock']) ?>" value="1">
        </div>
        <div class="action-buttons">
          <button class="btn add-to-cart-detail" data-product="<?= (int)$product['id'] ?>">Add to cart</button>
          <button class="icon-btn wishlist-btn" data-product="<?= (int)$product['id'] ?>"><i class="fa-regular fa-heart"></i></button>
        </div>
      </div>
    </div>
  </div>

  <div class="panel-card details-box" style="margin-top:18px">
    <h3>Product Details</h3>
    <p><?= e($product['description']) ?></p>
    <ul>
      <li>Freshly sourced and quality checked</li>
      <li>Fast delivery with safe packaging</li>
      <li>Easy returns for damaged items</li>
    </ul>
  </div>

  <div class="form-card" style="margin-top:24px">
    <h3>Customer Reviews</h3>
    <?php if (empty($reviews)): ?><p class="empty-note">No reviews yet. Be the first to review this product.</p><?php endif; ?>
    <?php foreach ($reviews as $r): ?>
      <p><strong><?= e($r['full_name']) ?></strong> <span class="stars-gold"><?= str_repeat('&#9733;', (int)$r['rating']) . str_repeat('&#9734;', 5 - (int)$r['rating']) ?></span><br><?= e($r['comment']) ?></p>
    <?php endforeach; ?>
    <?php if (isUserLoggedIn()): ?>
      <form method="post" action="/grocery/review_submit.php" class="form-grid">
        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
        <div>
          <label>Rating</label>
          <div class="star-input">
            <input type="radio" id="star5" name="rating" value="5" checked><label for="star5">&#9733;</label>
            <input type="radio" id="star4" name="rating" value="4"><label for="star4">&#9733;</label>
            <input type="radio" id="star3" name="rating" value="3"><label for="star3">&#9733;</label>
            <input type="radio" id="star2" name="rating" value="2"><label for="star2">&#9733;</label>
            <input type="radio" id="star1" name="rating" value="1"><label for="star1">&#9733;</label>
          </div>
        </div>
        <div><label>Comment</label><input name="comment" required></div>
        <button class="btn">Submit Review</button>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
