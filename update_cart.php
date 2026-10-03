<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isUserLoggedIn()) { echo json_encode(['success'=>false]); exit; }
$cartId = (int)($_POST['cart_id'] ?? 0);
$action = $_POST['action'] ?? 'inc';
if ($action === 'inc') {
  $stmt = $pdo->prepare('UPDATE cart SET quantity=quantity+1 WHERE id=? AND user_id=?');
  $stmt->execute([$cartId, $_SESSION['user_id']]);
} elseif ($action === 'dec') {
  $stmt = $pdo->prepare('UPDATE cart SET quantity=GREATEST(quantity-1,1) WHERE id=? AND user_id=?');
  $stmt->execute([$cartId, $_SESSION['user_id']]);
} elseif ($action === 'remove') {
  $stmt = $pdo->prepare('DELETE FROM cart WHERE id=? AND user_id=?');
  $stmt->execute([$cartId, $_SESSION['user_id']]);
}
echo json_encode(['success'=>true]);
?>
