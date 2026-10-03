<?php
$pageTitle='Register';
require_once __DIR__ . '/../includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $password = $_POST['password'] ?? '';
  if (strlen($password) < 6) { $error='Password must be at least 6 characters.'; }
  else {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)');
    try { $stmt->execute([$name, $email, $phone, $hash]); header('Location: /grocery/auth/login.php'); exit; }
    catch (PDOException $e) { $error='Email already exists.'; }
  }
}
?>
<section class="container" style="padding:40px 0"><div class="form-card" style="max-width:460px;margin:auto"><h2>Create Account</h2><?php if(!empty($error)) echo '<p style="color:red">'.e($error).'</p>'; ?><form method="post" class="form-grid"><div><label>Name</label><input name="full_name" required></div><div><label>Phone</label><input name="phone"></div><div><label>Email</label><input type="email" name="email" required></div><div><label>Password</label><input type="password" name="password" required></div><button class="btn">Register</button></form></div></section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
