<?php
$pageTitle='User Dashboard';
require_once __DIR__ . '/../includes/header.php';
requireUserAuth();
?>
<section class="container" style="padding:24px 0">
  <div class="admin-layout">
    <aside class="sidebar" id="userDashboardSidebar">
      <h3>My Account</h3>
      <a class="active" href="#overview" data-dashboard-section="overview"><i class="fa-solid fa-chart-simple"></i>Overview</a>
      <a href="#orders" data-dashboard-section="orders"><i class="fa-solid fa-bag-shopping"></i>Orders</a>
      <a href="#wishlist" data-dashboard-section="wishlist"><i class="fa-regular fa-heart"></i>Wishlist</a>
      <a href="#cart" data-dashboard-section="cart"><i class="fa-solid fa-cart-shopping"></i>Cart</a>
      <a href="/grocery/auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i>Logout</a>
    </aside>
    <div class="content-shell" id="userDashboardContent" data-dashboard-container="1"></div>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
