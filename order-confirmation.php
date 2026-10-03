<?php
$pageTitle='Order Confirmation';
require_once __DIR__ . '/includes/header.php';
requireUserAuth();

$orderNo = $_GET['order'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_number=? AND user_id=? LIMIT 1');
$stmt->execute([$orderNo, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
  echo '<section class="container" style="padding:24px 0"><div class="form-card"><h2>Order Confirmation</h2><p>Order not found.</p><a class="btn" href="/grocery/order-history.php">View Orders</a></div></section>';
  require_once __DIR__ . '/includes/footer.php';
  exit;
}

$itemsStmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?');
$itemsStmt->execute([$order['id']]);
$items = $itemsStmt->fetchAll();
?>
<section class="container" style="padding:24px 0">
  <div class="hero-card">
    <div class="panel-card">
      <h2 style="margin-top:0">Order Confirmed</h2>
      <p>Your payment was successful and your order has been placed.</p>
      <p><strong>Order Number:</strong> <?= e($order['order_number']) ?></p>
      <p><strong>Payment Method:</strong> <?= strtoupper(e($order['payment_method'])) ?></p>
      <p><strong>Status:</strong> <?= e($order['status']) ?></p>
      <a class="btn" href="/grocery/tracking.php?order=<?= urlencode($order['order_number']) ?>">Track Order</a>
      <a class="btn" style="background:#0f766e" href="/grocery/shop.php">Continue Shopping</a>
    </div>
    <div class="panel-card">
      <h3>Final Summary</h3>
      <?php foreach($items as $item): ?>
        <p><?= e($item['name']) ?> x <?= (int)$item['quantity'] ?> = Rs <?= number_format((float)$item['price'] * (int)$item['quantity'],2) ?></p>
      <?php endforeach; ?>
      <hr>
      <p>Subtotal: Rs <?= number_format((float)$order['subtotal'],2) ?></p>
      <p>Discount: Rs <?= number_format((float)$order['discount'],2) ?></p>
      <p>Delivery: Rs <?= number_format((float)$order['delivery_fee'],2) ?></p>
      <h3>Total Paid: Rs <?= number_format((float)$order['total_amount'],2) ?></h3>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
