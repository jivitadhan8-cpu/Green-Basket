<?php
$pageTitle='Checkout';
require_once __DIR__ . '/includes/header.php';
requireUserAuth();
ensureCouponSchema($pdo);
ensurePaymentSchema($pdo);

if (!isset($_SESSION['checkout_token'])) {
  $_SESSION['checkout_token'] = bin2hex(random_bytes(16));
}
if (!isset($_SESSION['checkout_completed_tokens'])) {
  $_SESSION['checkout_completed_tokens'] = [];
}

$userStmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

$cartStmt = $pdo->prepare('SELECT c.*, p.name, p.price FROM cart c JOIN products p ON p.id = c.product_id WHERE c.user_id = ?');
$cartStmt->execute([$_SESSION['user_id']]);
$items = $cartStmt->fetchAll();

if (empty($items)) {
  echo '<section class="container" style="padding:24px 0"><div class="form-card"><h2>Checkout</h2><p>Your cart is empty.</p><a class="btn" href="/grocery/shop.php">Continue Shopping</a></div></section>';
  require_once __DIR__ . '/includes/footer.php';
  exit;
}

$subtotal = 0;
foreach ($items as $i) { $subtotal += $i['price'] * $i['quantity']; }
$delivery = $subtotal > 499 ? 0 : 30;
$discount = 0;
$total = $subtotal + $delivery;
$couponMessage = '';
$couponError = false;
$appliedCouponCode = '';
$paymentError = '';
$generalError = '';

function validateUpi(string $upi): bool {
  return (bool)preg_match('/^[a-zA-Z0-9._-]{2,64}@[a-zA-Z]{2,64}$/', $upi);
}

function sanitizeCardNumber(string $value): string {
  return preg_replace('/\D+/', '', $value);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $checkoutToken = $_POST['checkout_token'] ?? '';
  if ($checkoutToken === '' || !hash_equals($_SESSION['checkout_token'], $checkoutToken)) {
    $generalError = 'Invalid checkout session. Please refresh and try again.';
  } elseif (in_array($checkoutToken, $_SESSION['checkout_completed_tokens'], true)) {
    $generalError = 'This order is already processed. Please do not submit again.';
  }

  $couponCode = strtoupper(trim($_POST['coupon_code'] ?? ''));
  if ($couponCode !== '') {
    $cstmt = $pdo->prepare('SELECT * FROM coupons WHERE code=? LIMIT 1');
    $cstmt->execute([$couponCode]);
    $coupon = $cstmt->fetch();

    if (!$coupon) {
      $couponMessage = 'Invalid coupon code.';
      $couponError = true;
    } elseif ((int)$coupon['is_active'] !== 1) {
      $couponMessage = 'This coupon is inactive.';
      $couponError = true;
    } elseif (!empty($coupon['valid_until']) && strtotime($coupon['valid_until']) < strtotime(date('Y-m-d'))) {
      $couponMessage = 'This coupon has expired.';
      $couponError = true;
    } elseif ((int)$coupon['usage_limit'] > 0 && (int)$coupon['used_count'] >= (int)$coupon['usage_limit']) {
      $couponMessage = 'Coupon usage limit reached.';
      $couponError = true;
    } elseif ($subtotal < (float)$coupon['min_order_value']) {
      $couponMessage = 'Minimum order for this coupon is Rs ' . number_format((float)$coupon['min_order_value'], 2);
      $couponError = true;
    } else {
      $discount = round($subtotal * ((float)$coupon['discount_value'] / 100), 2);
      $appliedCouponCode = $couponCode;
      $couponMessage = 'Coupon applied successfully.';
    }
  }

  $total = max(0, $subtotal - $discount + $delivery);

  $paymentMethod = $_POST['payment_method'] ?? 'card';
  $paymentVerified = ($_POST['payment_verified'] ?? '0') === '1';
  $transactionId = trim($_POST['transaction_id'] ?? '');

  if (!$couponError && $generalError === '') {
    if ($paymentMethod === 'cod') {
      if (!$paymentVerified) {
        $paymentError = 'Please confirm Cash on Delivery to proceed.';
      }
      if ($transactionId === '') {
        $transactionId = 'COD-' . strtoupper(bin2hex(random_bytes(4)));
      }
    } elseif ($paymentMethod === 'upi') {
      $upiId = trim($_POST['upi_id'] ?? '');
      $upiPin = trim($_POST['upi_pin'] ?? '');
      if (!validateUpi($upiId)) {
        $paymentError = 'Please enter a valid UPI ID';
      } elseif ($upiPin === '') {
        $paymentError = 'UPI PIN is required';
      } elseif (!preg_match('/^\d{4,6}$/', $upiPin)) {
        $paymentError = 'UPI PIN must contain 4-6 digits';
      } elseif (!$paymentVerified) {
        $paymentError = 'Please complete UPI authentication.';
      }
      if ($transactionId === '') {
        $transactionId = 'UPI-' . strtoupper(bin2hex(random_bytes(5)));
      }
    } elseif ($paymentMethod === 'card') {
      $cardName = trim($_POST['card_name'] ?? '');
      $cardNumber = sanitizeCardNumber($_POST['card_number'] ?? '');
      $expiry = trim($_POST['card_expiry'] ?? '');
      $cvv = trim($_POST['card_cvv'] ?? '');

      if ($cardName === '') {
        $paymentError = 'Cardholder name is required.';
      } elseif (!preg_match('/^[A-Za-z ]+$/', $cardName)) {
        $paymentError = 'Cardholder name must contain letters and spaces only.';
      } elseif (strlen($cardNumber) !== 16) {
        $paymentError = 'Card number must contain exactly 16 digits';
      } elseif (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiry)) {
        $paymentError = 'Expiry must be in MM/YY format.';
      } elseif (!preg_match('/^\d{3}$/', $cvv)) {
        $paymentError = 'CVV must contain exactly 3 digits';
      } else {
        [$mm, $yy] = explode('/', $expiry);
        $expMonth = (int)$mm;
        $expYear = 2000 + (int)$yy;
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');
        if ($expYear < $currentYear || ($expYear === $currentYear && $expMonth < $currentMonth)) {
          $paymentError = 'Card is expired.';
        }
      }

      if ($paymentError === '' && !$paymentVerified) {
        $paymentError = 'Please complete card payment processing.';
      }
      if ($paymentError === '' && $transactionId === '') {
        $transactionId = 'CRD-' . strtoupper(bin2hex(random_bytes(5)));
      }
    } else {
      $paymentError = 'Invalid payment method selected.';
    }
  }

  if (!$couponError && $paymentError === '' && $generalError === '') {
    $pdo->beginTransaction();
    try {
      $orderNo = 'GB' . date('YmdHis') . rand(10,99);
      $orderStmt = $pdo->prepare('INSERT INTO orders (user_id, order_number, subtotal, discount, delivery_fee, total_amount, delivery_address, city, pincode, payment_method, payment_status, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "paid", "placed")');
      $orderStmt->execute([
        $_SESSION['user_id'], $orderNo, $subtotal, $discount, $delivery, $total,
        trim($_POST['address'] ?? ''), trim($_POST['city'] ?? ''), trim($_POST['pincode'] ?? ''), $paymentMethod
      ]);

      $orderId = (int)$pdo->lastInsertId();
      $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
      foreach ($items as $item) {
        $itemStmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
        $pdo->prepare('UPDATE products SET stock = GREATEST(stock-?,0) WHERE id=?')->execute([$item['quantity'], $item['product_id']]);
      }

      $pdo->prepare('INSERT INTO payments (order_id, payment_method, payment_status, transaction_id) VALUES (?, ?, "success", ?)')->execute([$orderId, $paymentMethod, $transactionId]);

      if ($appliedCouponCode !== '') {
        $pdo->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE code=?')->execute([$appliedCouponCode]);
      }

      $pdo->prepare('DELETE FROM cart WHERE user_id=?')->execute([$_SESSION['user_id']]);
      $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)')->execute([$_SESSION['user_id'], 'Order placed', 'Your order ' . $orderNo . ' has been confirmed.']);

      $_SESSION['checkout_completed_tokens'][] = $checkoutToken;
      $_SESSION['checkout_token'] = bin2hex(random_bytes(16));

      $pdo->commit();
      header('Location: /grocery/order-confirmation.php?order=' . urlencode($orderNo));
      exit;
    } catch (Throwable $e) {
      $pdo->rollBack();
      $generalError = 'Checkout failed. Please try again.';
    }
  }
}
?>
<section class="container" style="padding:25px 0">
  <h2>Checkout</h2>
  <div class="hero-card">
    <div class="form-card">
      <h3>Shipping & Payment</h3>
      <?php if ($generalError !== ''): ?><p style="color:#dc2626;font-weight:600;"><?= e($generalError) ?></p><?php endif; ?>
      <?php if ($couponMessage !== ''): ?><p style="color:<?= $couponError ? '#dc2626' : '#15803d' ?>;font-weight:600;"><?= e($couponMessage) ?></p><?php endif; ?>
      <?php if ($paymentError !== ''): ?><p style="color:#dc2626;font-weight:600;"><?= e($paymentError) ?></p><?php endif; ?>

      <form method="post" id="checkoutForm" class="form-grid">
        <input type="hidden" name="checkout_token" value="<?= e($_SESSION['checkout_token']) ?>">
        <input type="hidden" name="payment_verified" id="paymentVerified" value="0">
        <input type="hidden" name="transaction_id" id="transactionId" value="">

        <div><label>Address</label><textarea name="address" required><?= e($_POST['address'] ?? ($user['address'] ?? '')) ?></textarea></div>
        <div><label>City</label><input name="city" value="<?= e($_POST['city'] ?? ($user['city'] ?? '')) ?>" required></div>
        <div><label>Pincode</label><input name="pincode" value="<?= e($_POST['pincode'] ?? ($user['pincode'] ?? '')) ?>" required></div>
        <div><label>Coupon</label><input name="coupon_code" value="<?= e($_POST['coupon_code'] ?? '') ?>" placeholder="WELCOME10"></div>

        <div>
          <label>Payment Method</label>
          <select name="payment_method" id="paymentMethod">
            <option value="card" <?= (($_POST['payment_method'] ?? 'card') === 'card') ? 'selected' : '' ?>>Card</option>
            <option value="upi" <?= (($_POST['payment_method'] ?? '') === 'upi') ? 'selected' : '' ?>>UPI</option>
            <option value="cod" <?= (($_POST['payment_method'] ?? '') === 'cod') ? 'selected' : '' ?>>Cash on Delivery</option>
          </select>
        </div>

        <div id="codBox" class="panel-card payment-step" style="display:none;grid-column:1/-1;">
          <p style="margin:0;">You will pay when your order arrives.</p>
        </div>

        <div id="upiBox" class="panel-card payment-step" style="display:none;grid-column:1/-1;">
          <label>UPI ID</label>
          <input type="text" name="upi_id" id="upiId" value="<?= e($_POST['upi_id'] ?? '') ?>" placeholder="Enter UPI ID">
          <small class="field-error" id="upiIdError"></small>
          <div style="display:flex;gap:10px;margin-top:10px;align-items:center;">
            <button class="btn" type="button" id="continueUpiBtn">Continue</button>
          </div>
          <div id="upiPinStep" class="payment-step" style="display:none;margin-top:12px;">
            <label>UPI PIN</label>
            <input type="password" name="upi_pin" id="upiPin" inputmode="numeric" maxlength="6" value="<?= e($_POST['upi_pin'] ?? '') ?>" placeholder="Enter UPI PIN">
            <small class="field-error" id="upiPinError"></small>
          </div>
        </div>

        <div id="cardBox" class="panel-card payment-step" style="display:none;grid-column:1/-1;">
          <div class="form-grid">
            <div><label>Cardholder Name</label><input type="text" name="card_name" id="cardName" value="<?= e($_POST['card_name'] ?? '') ?>" placeholder="Cardholder name"><small class="field-error" id="cardNameError"></small></div>
            <div><label>Card Number</label><input type="text" name="card_number" id="cardNumber" maxlength="16" value="<?= e($_POST['card_number'] ?? '') ?>" placeholder="Card number"><small class="field-error" id="cardNumberError"></small></div>
            <div><label>Expiry (MM/YY)</label><input type="text" name="card_expiry" id="cardExpiry" maxlength="5" value="<?= e($_POST['card_expiry'] ?? '') ?>" placeholder="MM/YY"><small class="field-error" id="cardExpiryError"></small></div>
            <div><label>CVV</label><input type="password" name="card_cvv" id="cardCvv" maxlength="3" value="<?= e($_POST['card_cvv'] ?? '') ?>" placeholder="CVV"><small class="field-error" id="cardCvvError"></small></div>
          </div>
        </div>

        <button class="btn" id="placeOrderBtn">Proceed & Pay</button>
      </form>
    </div>

    <div class="panel-card">
      <h3>Final Order Summary</h3>
      <?php foreach($items as $item): ?><p><?= e($item['name']) ?> x <?= $item['quantity'] ?> = Rs <?= number_format($item['price'] * $item['quantity'], 2) ?></p><?php endforeach; ?>
      <hr>
      <p>Subtotal: Rs <?= number_format($subtotal,2) ?></p>
      <p>Delivery: Rs <?= number_format($delivery,2) ?></p>
      <p>Discount: Rs <?= number_format($discount,2) ?></p>
      <h3>Total: Rs <span id="checkoutTotal"><?= number_format($total,2) ?></span></h3>
    </div>
  </div>
</section>

<div id="paymentOverlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:3000;align-items:center;justify-content:center;">
  <div class="panel-card" style="max-width:420px;text-align:center;">
    <div class="loader" style="margin:0 auto 12px;"></div>
    <h3 id="overlayTitle">Processing Payment...</h3>
    <p id="overlayText" class="empty-note">Please wait while we process your payment.</p>
  </div>
</div>

<script>
(() => {
  const form = document.getElementById('checkoutForm');
  if (!form) return;
  const paymentMethod = document.getElementById('paymentMethod');
  const codBox = document.getElementById('codBox');
  const upiBox = document.getElementById('upiBox');
  const cardBox = document.getElementById('cardBox');
  const upiPinStep = document.getElementById('upiPinStep');
  const upiId = document.getElementById('upiId');
  const upiPin = document.getElementById('upiPin');
  const continueUpiBtn = document.getElementById('continueUpiBtn');
  const paymentVerified = document.getElementById('paymentVerified');
  const transactionId = document.getElementById('transactionId');
  const placeOrderBtn = document.getElementById('placeOrderBtn');
  const overlay = document.getElementById('paymentOverlay');
  const overlayTitle = document.getElementById('overlayTitle');
  const overlayText = document.getElementById('overlayText');

  const cardName = document.getElementById('cardName');
  const cardNumber = document.getElementById('cardNumber');
  const cardExpiry = document.getElementById('cardExpiry');
  const cardCvv = document.getElementById('cardCvv');

  const errors = {
    upiId: document.getElementById('upiIdError'),
    upiPin: document.getElementById('upiPinError'),
    cardName: document.getElementById('cardNameError'),
    cardNumber: document.getElementById('cardNumberError'),
    cardExpiry: document.getElementById('cardExpiryError'),
    cardCvv: document.getElementById('cardCvvError')
  };

  let isProcessing = false;
  let upiStepUnlocked = false;

  const setError = (el, msg) => { if (el) el.textContent = msg || ''; };
  const validateUpiId = (value) => /^[a-zA-Z0-9._-]{2,64}@[a-zA-Z]{2,64}$/.test(value.trim());
  const validateUpiPin = (value) => /^\d{4,6}$/.test(value);
  const validateCardName = (value) => /^[A-Za-z ]+$/.test(value.trim());
  const validateCardNumber = (value) => /^\d{16}$/.test(value);
  const validateCvv = (value) => /^\d{3}$/.test(value);
  const validateExpiry = (value) => {
    if (!/^(0[1-9]|1[0-2])\/(\d{2})$/.test(value)) return false;
    const [m, y] = value.split('/');
    const month = parseInt(m, 10);
    const year = 2000 + parseInt(y, 10);
    const now = new Date();
    const cm = now.getMonth() + 1;
    const cy = now.getFullYear();
    return !(year < cy || (year === cy && month < cm));
  };

  const showOverlay = (title, text) => {
    overlayTitle.textContent = title;
    overlayText.textContent = text;
    overlay.style.display = 'flex';
  };

  const resetPaymentState = () => {
    paymentVerified.value = '0';
    transactionId.value = '';
    upiStepUnlocked = false;
    if (upiPinStep) upiPinStep.style.display = 'none';
    Object.values(errors).forEach(el => setError(el, ''));
  };

  const validateCardLive = () => {
    if (!cardName || !cardNumber || !cardExpiry || !cardCvv) return false;
    const name = cardName.value.trim();
    const number = cardNumber.value;
    const expiry = cardExpiry.value;
    const cvv = cardCvv.value;

    setError(errors.cardName, name && !validateCardName(name) ? 'Card holder name can contain letters and spaces only' : '');
    setError(errors.cardNumber, number && !validateCardNumber(number) ? 'Card number must contain exactly 16 digits' : '');
    setError(errors.cardExpiry, expiry && !validateExpiry(expiry) ? 'Enter a valid expiry date (MM/YY)' : '');
    setError(errors.cardCvv, cvv && !validateCvv(cvv) ? 'CVV must contain exactly 3 digits' : '');

    return validateCardName(name) && validateCardNumber(number) && validateExpiry(expiry) && validateCvv(cvv);
  };

  const validateUpiLive = () => {
    if (!upiId || !upiPin) return false;
    const idValid = validateUpiId(upiId.value);
    setError(errors.upiId, upiId.value && !idValid ? 'Please enter a valid UPI ID' : '');
    if (!upiStepUnlocked) return false;
    const pin = upiPin.value;
    if (pin === '') setError(errors.upiPin, 'UPI PIN is required');
    else if (!validateUpiPin(pin)) setError(errors.upiPin, 'UPI PIN must contain 4-6 digits');
    else setError(errors.upiPin, '');
    return idValid && validateUpiPin(pin);
  };

  const updateProceedState = () => {
    const method = paymentMethod.value;
    if (method === 'card') placeOrderBtn.disabled = !validateCardLive();
    else if (method === 'upi') placeOrderBtn.disabled = !validateUpiLive();
    else placeOrderBtn.disabled = false;
  };

  const togglePaymentFields = () => {
    const method = paymentMethod.value;
    codBox.style.display = method === 'cod' ? 'block' : 'none';
    upiBox.style.display = method === 'upi' ? 'block' : 'none';
    cardBox.style.display = method === 'card' ? 'block' : 'none';
    resetPaymentState();
    updateProceedState();
  };

  continueUpiBtn?.addEventListener('click', () => {
    if (!validateUpiId(upiId.value)) {
      setError(errors.upiId, 'Please enter a valid UPI ID');
      return;
    }
    setError(errors.upiId, '');
    upiStepUnlocked = true;
    upiPinStep.style.display = 'block';
    upiPin.focus();
    updateProceedState();
  });

  upiId?.addEventListener('input', () => {
    upiStepUnlocked = false;
    upiPinStep.style.display = 'none';
    paymentVerified.value = '0';
    transactionId.value = '';
    setError(errors.upiId, '');
    setError(errors.upiPin, '');
    updateProceedState();
  });

  upiPin?.addEventListener('input', () => {
    upiPin.value = upiPin.value.replace(/\D/g, '').slice(0, 6);
    paymentVerified.value = '0';
    transactionId.value = '';
    updateProceedState();
  });

  cardName?.addEventListener('input', () => {
    cardName.value = cardName.value.replace(/[^A-Za-z ]+/g, '');
    paymentVerified.value = '0';
    transactionId.value = '';
    updateProceedState();
  });

  cardNumber?.addEventListener('input', () => {
    cardNumber.value = cardNumber.value.replace(/\D/g, '').slice(0, 16);
    paymentVerified.value = '0';
    transactionId.value = '';
    updateProceedState();
  });

  cardCvv?.addEventListener('input', () => {
    cardCvv.value = cardCvv.value.replace(/\D/g, '').slice(0, 3);
    paymentVerified.value = '0';
    transactionId.value = '';
    updateProceedState();
  });

  cardExpiry?.addEventListener('input', () => {
    let raw = cardExpiry.value.replace(/\D/g, '').slice(0, 4);
    if (raw.length >= 3) raw = raw.slice(0, 2) + '/' + raw.slice(2);
    cardExpiry.value = raw;
    paymentVerified.value = '0';
    transactionId.value = '';
    updateProceedState();
  });

  paymentMethod.addEventListener('change', togglePaymentFields);
  togglePaymentFields();

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (isProcessing) return;

    const method = paymentMethod.value;
    if (method === 'upi') {
      if (!validateUpiId(upiId.value)) { setError(errors.upiId, 'Please enter a valid UPI ID'); return; }
      if (!upiStepUnlocked) { setError(errors.upiPin, 'UPI PIN is required'); return; }
      if (!validateUpiPin(upiPin.value)) {
        setError(errors.upiPin, upiPin.value ? 'UPI PIN must contain 4-6 digits' : 'UPI PIN is required');
        return;
      }
    }

    if (method === 'card' && !validateCardLive()) return;

    isProcessing = true;
    placeOrderBtn.disabled = true;

    if (method === 'cod') {
      paymentVerified.value = '1';
      transactionId.value = 'COD-' + Math.random().toString(36).slice(2, 10).toUpperCase();
      showOverlay('Confirming Order...', 'You will pay when your order arrives.');
      await new Promise(r => setTimeout(r, 1200));
      form.submit();
      return;
    }

    if (method === 'upi') {
      paymentVerified.value = '1';
      transactionId.value = 'UPI-' + Math.random().toString(36).slice(2, 10).toUpperCase();
      showOverlay('Processing UPI Payment...', 'Authenticating UPI PIN and processing payment.');
      await new Promise(r => setTimeout(r, 1800));
      overlayTitle.textContent = 'Payment Successful';
      overlayText.textContent = 'UPI payment completed. Creating your order.';
      await new Promise(r => setTimeout(r, 900));
      form.submit();
      return;
    }

    paymentVerified.value = '1';
    transactionId.value = 'CRD-' + Math.random().toString(36).slice(2, 10).toUpperCase();
    showOverlay('Processing Card Payment...', 'Validating card details and processing payment.');
    await new Promise(r => setTimeout(r, 2000));
    overlayTitle.textContent = 'Payment Successful';
    overlayText.textContent = 'Card payment completed. Creating your order.';
    await new Promise(r => setTimeout(r, 900));
    form.submit();
  });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
