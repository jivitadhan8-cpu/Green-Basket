<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isUserLoggedIn()) { echo json_encode(['success'=>false]); exit; }
$userId = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT c.id, c.quantity, p.name, p.price, p.image FROM cart c JOIN products p ON p.id=c.product_id WHERE c.user_id=?');
$stmt->execute([$userId]);
$items = $stmt->fetchAll();
echo json_encode(['success'=>true,'items'=>$items,'total_qty'=>getCartCount($pdo,$userId)]);
?>
