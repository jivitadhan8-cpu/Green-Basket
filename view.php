<?php
$pageTitle='Order Details';
require_once __DIR__ . '/../includes/header.php';

$orderId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT o.*, u.full_name, u.email, u.phone FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=? LIMIT 1');
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
  echo '<p>Order not found.</p>';
  require_once __DIR__ . '/../includes/footer.php';
  exit;
}

$itemsStmt = $pdo->prepare('SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?');
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();
?>
<h2>Order #<?= e($order['order_number']) ?></h2>
<div class="hero-card" style="margin-bottom:14px;grid-template-columns:1fr 1fr;">
  <div class="panel-card">
    <h3>Customer</h3>
    <p><strong>Name:</strong> <?= e($order['full_name']) ?></p>
    <p><strong>Email:</strong> <?= e($order['email']) ?></p>
    <p><strong>Phone:</strong> <?= e($order['phone'] ?? '') ?></p>
    <p><strong>Status:</strong> <?= e($order['status']) ?></p>
    <p><strong>Payment:</strong> <?= e($order['payment_method']) ?> (<?= e($order['payment_status']) ?>)</p>
  </div>
  <div class="panel-card">
    <h3>Delivery Address</h3>
    <p><?= nl2br(e($order['delivery_address'])) ?></p>
    <p><?= e($order['city']) ?> - <?= e($order['pincode']) ?></p>
    <h4>Bill Summary</h4>
    <p>Subtotal: Rs <?= e((string)$order['subtotal']) ?></p>
    <p>Discount: Rs <?= e((string)$order['discount']) ?></p>
    <p>Delivery Fee: Rs <?= e((string)$order['delivery_fee']) ?></p>
    <p><strong>Total: Rs <?= e((string)$order['total_amount']) ?></strong></p>
  </div>
</div>
<div class="table-wrap">
  <h3>Order Items</h3>
  <table>
    <tr><th>Product</th><th>Price</th><th>Qty</th><th>Line Total</th></tr>
    <?php foreach ($items as $item): ?>
      <tr>
        <td><?= e($item['name']) ?></td>
        <td>Rs <?= e((string)$item['price']) ?></td>
        <td><?= (int)$item['quantity'] ?></td>
        <td>Rs <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<p style="margin-top:12px;"><a href="/grocery/admin/orders/index.php">? Back to orders</a></p>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
