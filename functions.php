<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

function getCategories(PDO $pdo): array {
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
    return $stmt->fetchAll();
}

function getProducts(PDO $pdo, string $where = '1=1', array $params = [], int $limit = 12): array {
    $sql = "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE $where ORDER BY p.created_at DESC LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getCartCount(PDO $pdo, int $userId): int {
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity),0) total FROM cart WHERE user_id = ?');
    $stmt->execute([$userId]);
    return (int)$stmt->fetch()['total'];
}

function getWishlistCount(PDO $pdo, int $userId): int {
    $stmt = $pdo->prepare('SELECT COUNT(*) total FROM wishlist WHERE user_id = ?');
    $stmt->execute([$userId]);
    return (int)$stmt->fetch()['total'];
}

function ensureCouponSchema(PDO $pdo): void {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $dbName = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();
        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?');

        $checkStmt->execute([$dbName, 'coupons', 'usage_limit']);
        if ((int)$checkStmt->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE coupons ADD COLUMN usage_limit INT DEFAULT 0");
        }

        $checkStmt->execute([$dbName, 'coupons', 'used_count']);
        if ((int)$checkStmt->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE coupons ADD COLUMN used_count INT DEFAULT 0");
        }
    } catch (PDOException $e) {
        // Keep app running on older schemas/engines.
    }
}

function ensureContactMessageSchema(PDO $pdo): void {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(120) NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    } catch (PDOException $e) {
        // Keep app running.
    }
}

function ensurePaymentSchema(PDO $pdo): void {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
            payment_id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            payment_method VARCHAR(20) NOT NULL,
            payment_status ENUM('pending','success','failed') DEFAULT 'pending',
            transaction_id VARCHAR(80) NOT NULL,
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )");
    } catch (PDOException $e) {
        // Keep app running.
    }
}
?>
