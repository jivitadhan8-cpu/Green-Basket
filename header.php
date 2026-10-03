<?php
require_once __DIR__ . '/functions.php';
$userId = $_SESSION['user_id'] ?? null;
$cartCount = $userId ? getCartCount($pdo, $userId) : 0;
$wishlistCount = $userId ? getWishlistCount($pdo, $userId) : 0;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' | GreenBasket' : 'GreenBasket - Grocery Delivery' ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="/grocery/assets/css/style.css">
</head>
<body>
  <header class="topbar glass">
    <div class="container nav-wrap">
      <a class="logo" href="/grocery/index.php"><i class="fa-solid fa-basket-shopping"></i> GreenBasket</a>
      <form class="search-wrap" action="/grocery/shop.php" method="get">
        <input type="text" id="live-search" name="q" placeholder="Search vegetables, fruits, dairy..." autocomplete="off">
        <div id="search-suggestions" class="suggestions"></div>
      </form>
      <nav class="nav-links">
        <a href="/grocery/shop.php">Shop</a>
        <a href="/grocery/contact.php">Contact</a>
        <a href="/grocery/user/dashboard.php">Dashboard</a>
      </nav>
      <div class="nav-actions">
        <button id="darkModeToggle" class="icon-btn"><i class="fa-solid fa-moon"></i></button>
        <a class="icon-btn" href="/grocery/wishlist.php"><i class="fa-regular fa-heart"></i><span><?= $wishlistCount ?></span></a>
        <button class="icon-btn" id="cartToggle"><i class="fa-solid fa-cart-shopping"></i><span id="cartCount"><?= $cartCount ?></span></button>
        <?php if (isUserLoggedIn()): ?>
            <a class="btn btn-sm" href="/grocery/auth/logout.php">Logout</a>
        <?php else: ?>
            <a class="btn btn-sm" href="/grocery/auth/login.php">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </header>
  <aside class="cart-sidebar" id="cartSidebar">
    <div class="cart-header">
      <h3>Your Cart</h3>
      <button id="closeCart"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div id="cartSidebarBody"></div>
    <a href="/grocery/cart.php" class="btn w-100">View Cart</a>
  </aside>
  <main>
