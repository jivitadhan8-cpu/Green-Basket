<?php
$pageTitle='Contact';
require_once __DIR__ . '/includes/header.php';
ensureContactMessageSchema($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $message = trim($_POST['message'] ?? '');

  if ($name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $message !== '') {
    $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, $message]);
    $success = 'Thanks! Our team will contact you soon.';
  } else {
    $error = 'Please fill all fields with a valid email.';
  }
}
?>
<section class="container" style="padding:24px 0"><div class="hero-card"><div><h2>Contact GreenBasket</h2><p>Need help with orders, refunds, or partnerships? Reach our support team.</p><p><i class="fa-solid fa-phone"></i> +91 99999 11111</p><p><i class="fa-solid fa-envelope"></i> hello@greenbasket.com</p></div><div class="form-card"><h3>Send Message</h3><?php if(!empty($success)) echo '<p style="color:green">'.e($success).'</p>'; ?><?php if(!empty($error)) echo '<p style="color:#dc2626">'.e($error).'</p>'; ?><form method="post" class="form-grid"><div><label>Name</label><input name="name" required></div><div><label>Email</label><input name="email" type="email" required></div><div><label>Message</label><textarea name="message" required></textarea></div><button class="btn">Send</button></form></div></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
