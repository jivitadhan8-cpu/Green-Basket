<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isUserLoggedIn()) { echo json_encode(['success'=>false,'message'=>'Please login first']); exit; }
$userId = (int)$_SESSION['user_id'];
$productId = (int)($_POST['product_id'] ?? 0);
$stmt = $pdo->prepare('SELECT id FROM wishlist WHERE user_id=? AND product_id=?');
$stmt->execute([$userId,$productId]);
$item = $stmt->fetch();
if ($item) {
  $pdo->prepare('DELETE FROM wishlist WHERE id=?')->execute([$item['id']]);
  echo json_encode(['success'=>true,'message'=>'Removed from wishlist']);
} else {
  $pdo->prepare('INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)')->execute([$userId,$productId]);
  echo json_encode(['success'=>true,'message'=>'Added to wishlist']);
}
?>
