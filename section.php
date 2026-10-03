<?php
require_once __DIR__ . '/../includes/functions.php';
requireUserAuth();

$userId = (int)$_SESSION['user_id'];
$section = $_GET['section'] ?? 'overview';
$allowed = ['overview', 'orders', 'wishlist', 'cart'];
if (!in_array($section, $allowed, true)) {
    $section = 'overview';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $section === 'overview') {
    $stmt = $pdo->prepare('UPDATE users SET full_name=?, phone=?, address=?, city=?, pincode=? WHERE id=?');
    $stmt->execute([
        trim($_POST['full_name'] ?? ''),
        trim($_POST['phone'] ?? ''),
        trim($_POST['address'] ?? ''),
        trim($_POST['city'] ?? ''),
        trim($_POST['pincode'] ?? ''),
        $userId
    ]);
}

if ($section === 'overview') {
    $user = $pdo->prepare('SELECT * FROM users WHERE id=?');
    $user->execute([$userId]);
    $profile = $user->fetch();

    $ordersCount = $pdo->prepare('SELECT COUNT(*) c FROM orders WHERE user_id=?');
    $ordersCount->execute([$userId]);

    $noti = $pdo->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 6');
    $noti->execute([$userId]);
    ?>
    <div class="stat-grid dashboard-stats">
      <div class="stat"><p>Orders</p><div class="counter"><?= $ordersCount->fetch()['c'] ?></div></div>
      <div class="stat"><p>Cart Items</p><div class="counter"><?= getCartCount($pdo,$userId) ?></div></div>
      <div class="stat"><p>Wishlist</p><div class="counter"><?= getWishlistCount($pdo,$userId) ?></div></div>
    </div>
    <div class="dashboard-panels">
      <div class="form-card">
        <h3>Profile Management</h3>
        <form method="post" action="/grocery/user/section.php?section=overview" class="form-grid dashboard-ajax-form">
          <div><label>Name</label><input name="full_name" value="<?= e($profile['full_name']) ?>" required></div>
          <div><label>Phone</label><input name="phone" value="<?= e($profile['phone'] ?? '') ?>"></div>
          <div><label>Address</label><textarea name="address"><?= e($profile['address'] ?? '') ?></textarea></div>
          <div><label>City</label><input name="city" value="<?= e($profile['city'] ?? '') ?>"></div>
          <div><label>Pincode</label><input name="pincode" value="<?= e($profile['pincode'] ?? '') ?>"></div>
          <button class="btn">Update Profile</button>
        </form>
      </div>
      <div class="panel-card">
        <h3>Notifications</h3>
        <?php foreach($noti->fetchAll() as $n): ?>
          <p><strong><?= e($n['title']) ?></strong><br><?= e($n['message']) ?></p>
        <?php endforeach; ?>
      </div>
    </div>
    <?php
    exit;
}

if ($section === 'orders') {
    $stmt = $pdo->prepare('SELECT order_number, placed_at, status, total_amount FROM orders WHERE user_id=? ORDER BY placed_at DESC');
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();
    ?>
    <div class="table-wrap"><h3>My Orders</h3><table><tr><th>Order</th><th>Date</th><th>Status</th><th>Total</th><th>Track</th></tr><?php foreach($orders as $o): ?><tr><td><?= e($o['order_number']) ?></td><td><?= e($o['placed_at']) ?></td><td><?= e($o['status']) ?></td><td>Rs <?= e((string)$o['total_amount']) ?></td><td><a href="/grocery/tracking.php?order=<?= urlencode($o['order_number']) ?>">Track</a></td></tr><?php endforeach; ?></table></div>
    <?php
    exit;
}

if ($section === 'wishlist') {
    $stmt = $pdo->prepare('SELECT p.* FROM wishlist w JOIN products p ON p.id = w.product_id WHERE w.user_id=? ORDER BY w.created_at DESC');
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();
    ?>
    <div class="grid product-grid">
      <?php foreach($items as $p): ?>
      <div class="card"><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>"><div class="card-body"><h4><?= e($p['name']) ?></h4><p class="price">Rs <?= e((string)$p['price']) ?></p><div style="display:flex;gap:8px"><button class="btn btn-sm add-to-cart-btn" data-product="<?= (int)$p['id'] ?>">Add</button><button class="icon-btn wishlist-btn" data-product="<?= (int)$p['id'] ?>"><i class="fa-regular fa-heart"></i></button></div></div></div>
      <?php endforeach; ?>
    </div>
    <?php
    exit;
}

if ($section === 'cart') {
    $stmt = $pdo->prepare('SELECT c.id, c.quantity, p.name, p.price, p.image FROM cart c JOIN products p ON p.id = c.product_id WHERE c.user_id=?');
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();
    $subtotal = 0;
    ?>
    <div class="table-wrap"><h3>My Cart</h3><table><tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr><?php foreach($items as $item): $line = $item['price'] * $item['quantity']; $subtotal += $line; ?><tr><td><?= e($item['name']) ?></td><td>Rs <?= e((string)$item['price']) ?></td><td><button onclick="updateCartQty(<?= (int)$item['id'] ?>,'dec')">-</button> <?= (int)$item['quantity'] ?> <button onclick="updateCartQty(<?= (int)$item['id'] ?>,'inc')">+</button></td><td>Rs <?= number_format($line,2) ?></td><td><button onclick="updateCartQty(<?= (int)$item['id'] ?>,'remove')">Remove</button></td></tr><?php endforeach; ?></table><h3 style="text-align:right">Subtotal: Rs <?= number_format($subtotal,2) ?></h3><a class="btn" href="/grocery/checkout.php">Proceed Checkout</a></div>
    <?php
    exit;
}
?>
