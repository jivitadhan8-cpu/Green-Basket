<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isUserLoggedIn()) { echo json_encode(['success' => false, 'message' => 'Please login first']); exit; }
$userId = (int)$_SESSION['user_id'];
$productId = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['quantity'] ?? 1));
$stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id=? AND product_id=?');
$stmt->execute([$userId, $productId]);
$cart = $stmt->fetch();
if ($cart) {
  $upd = $pdo->prepare('UPDATE cart SET quantity = quantity + ? WHERE id = ?');
  $upd->execute([$qty, $cart['id']]);
} else {
  $ins = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
  $ins->execute([$userId, $productId, $qty]);
}
$total = getCartCount($pdo, $userId);
echo json_encode(['success' => true, 'total_qty' => $total]);
?>
