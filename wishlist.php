<?php
$pageTitle='Wishlist';
require_once __DIR__ . '/includes/header.php';
requireUserAuth();
$stmt = $pdo->prepare('SELECT w.id, p.* FROM wishlist w JOIN products p ON p.id = w.product_id WHERE w.user_id=?');
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();
?>
<section class="container" style="padding:24px 0"><h2>Your Wishlist</h2><div class="grid product-grid"><?php foreach($items as $p): ?><div class="card"><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>"><div class="card-body"><h4><?= e($p['name']) ?></h4><p>Rs <?= e($p['price']) ?></p><button class="btn add-to-cart-btn" data-product="<?= $p['id'] ?>">Add to Cart</button></div></div><?php endforeach; ?></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
