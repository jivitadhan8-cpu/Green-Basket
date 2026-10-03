<?php
$pageTitle='Order Tracking';
require_once __DIR__ . '/includes/header.php';
requireUserAuth();
$orderNo = $_GET['order'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_number=? AND user_id=? LIMIT 1');
$stmt->execute([$orderNo, $_SESSION['user_id']]);
$order = $stmt->fetch();
$steps = ['placed','processing','packed','shipped','delivered'];
?>
<section class="container" style="padding:25px 0"><div class="form-card"><h2>Delivery Tracking</h2><?php if($order): ?><p>Order #<?= e($order['order_number']) ?> | Status: <strong><?= e($order['status']) ?></strong></p><div class="timeline"><?php foreach($steps as $s): ?><div class="step" style="opacity:<?= array_search($s,$steps)<=array_search($order['status'],$steps)?'1':'0.35' ?>"><h4><?= ucfirst($s) ?></h4><p><?= ucfirst($s) ?> update for your order.</p></div><?php endforeach; ?></div><?php else: ?><p>Order not found.</p><?php endif; ?></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
