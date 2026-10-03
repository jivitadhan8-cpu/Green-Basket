<?php
$pageTitle='Cart';
require_once __DIR__ . '/includes/header.php';
requireUserAuth();
$stmt = $pdo->prepare('SELECT c.id, c.quantity, p.name, p.price, p.image, p.stock FROM cart c JOIN products p ON p.id = c.product_id WHERE c.user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();
$subtotal = 0;
?>
<section class="container" style="padding:24px 0"><h2>Your Cart</h2><div class="table-wrap"><table><tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr><?php foreach($items as $item): $line = $item['price'] * $item['quantity']; $subtotal += $line; ?><tr><td><?= e($item['name']) ?></td><td>Rs <?= e($item['price']) ?></td><td><button onclick="updateCartQty(<?= $item['id'] ?>,'dec')">-</button> <?= $item['quantity'] ?> <button onclick="updateCartQty(<?= $item['id'] ?>,'inc')">+</button></td><td>Rs <?= number_format($line,2) ?></td><td><button onclick="updateCartQty(<?= $item['id'] ?>,'remove')">Remove</button></td></tr><?php endforeach; ?></table><h3 style="text-align:right">Subtotal: Rs <?= number_format($subtotal,2) ?></h3><a class="btn" href="/grocery/checkout.php">Proceed Checkout</a></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
