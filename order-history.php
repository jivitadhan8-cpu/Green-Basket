<?php
$pageTitle='Order History';
require_once __DIR__ . '/includes/header.php';
requireUserAuth();
$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY placed_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<section class="container" style="padding:25px 0"><h2>Order History</h2><div class="table-wrap"><table><tr><th>Order</th><th>Date</th><th>Status</th><th>Total</th><th></th></tr><?php foreach($orders as $o): ?><tr><td><?= e($o['order_number']) ?></td><td><?= e($o['placed_at']) ?></td><td><?= e($o['status']) ?></td><td>Rs <?= e($o['total_amount']) ?></td><td><a href="/grocery/tracking.php?order=<?= urlencode($o['order_number']) ?>">Track</a></td></tr><?php endforeach; ?></table></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
