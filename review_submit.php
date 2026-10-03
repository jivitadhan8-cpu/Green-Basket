<?php
require_once __DIR__ . '/includes/functions.php';
requireUserAuth();
$productId = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 5);
$comment = trim($_POST['comment'] ?? '');
$stmt = $pdo->prepare('INSERT INTO reviews (user_id, product_id, rating, comment, status) VALUES (?, ?, ?, ?, "approved")');
$stmt->execute([$_SESSION['user_id'], $productId, $rating, $comment]);
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/grocery/shop.php'));
exit;
?>
