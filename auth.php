<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isUserLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_id']);
}

function requireUserAuth(): void {
    if (!isUserLoggedIn()) {
        header('Location: /grocery/auth/login.php');
        exit;
    }
}

function requireAdminAuth(): void {
    if (!isAdminLoggedIn()) {
        header('Location: /grocery/admin/login.php');
        exit;
    }
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]+/', '-', $text);
    return trim($text, '-');
}

function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}
?>
