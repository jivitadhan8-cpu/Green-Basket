<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
$q = trim($_GET['q'] ?? '');
if ($q === '') { echo json_encode([]); exit; }
$stmt = $pdo->prepare('SELECT id, name, slug FROM products WHERE name LIKE ? LIMIT 8');
$stmt->execute(["%$q%"]);
echo json_encode($stmt->fetchAll());
?>
