<?php
$pageTitle='Login';
require_once __DIR__ . '/../includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
  $stmt->execute([$email]);
  $user = $stmt->fetch();
  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    header('Location: /grocery/index.php'); exit;
  }
  $error='Invalid credentials';
}
?>
<section class="container" style="padding:40px 0"><div class="form-card" style="max-width:420px;margin:auto"><h2>User Login</h2><?php if(!empty($error)) echo '<p style="color:red">'.e($error).'</p>'; ?><form method="post"><label>Email</label><input type="email" name="email" required><label>Password</label><input type="password" name="password" required><button class="btn w-100" style="margin-top:10px">Login</button><p>New user? <a href="/grocery/auth/register.php">Register</a></p></form></div></section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
