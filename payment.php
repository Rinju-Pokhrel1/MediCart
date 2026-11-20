<?php
require_once 'config.php';
require_login();
$id=(int)($_GET['order']??0);
$s=$pdo->prepare('SELECT * FROM orders WHERE id=? AND user_id=?');
$s->execute([$id,current_user()['id']]);
$order=$s->fetch();
if(!$order) exit('Order not found.');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($order['payment_status'] === 'paid') {

        $message = 'This order has already been paid. No further payment attempt is allowed.';

    } else {

        $result = $_POST['result'] ?? '';

        if (!in_array($result, ['success', 'failed'], true)) {

            $message = 'Invalid payment selection. Please try again.';

        } else {

            $status = $result;
            $paymentStatus = $status === 'success' ? 'paid' : 'failed';
            $reference = 'DEMO-' . $id . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));

            try {
                $pdo->beginTransaction();

                $pdo->prepare(
                    'INSERT INTO payments(order_id, method, status, reference)
                     VALUES(?, ?, ?, ?)'
                )->execute([
                    $id,
                    'MediCart Demo Gateway',
                    $status,
                    $reference
                ]);

                $pdo->prepare(
                    'UPDATE orders SET payment_status = ? WHERE id = ?'
                )->execute([
                    $paymentStatus,
                    $id
                ]);

                $pdo->commit();

                $order['payment_status'] = $paymentStatus;

                $message = 'Payment ' . $status .
                    '. This was a simulated transaction; no real money was transferred.';

            } catch (Throwable $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $message = 'Payment could not be processed. Please try again.';
            }
        }
    }
}
$pageTitle='Secure Demo Checkout | MediCart';
$metaDescription='MediCart educational sandbox payment page. Simulate successful or failed payments without real card details.';
require 'includes/header.php';
?>
<section class="pay-hero"><div><span class="eyebrow">MEDICART · SANDBOX CHECKOUT</span><h1>Complete your payment</h1><p>A safe, simulated payment experience for the CSC 381 E-Commerce lab.</p></div><div class="pay-shield">✓</div></section>
<?php if($message): ?><div class="success payment-result"><?= e($message) ?></div><?php endif; ?>
<div class="payment-layout">
 <section class="payment-panel"><div class="pay-panel-heading"><div><span class="eyebrow">PAYMENT METHOD</span><h2>Demo payment gateway</h2></div><span class="demo-badge">TEST MODE</span></div>
  <div class="gateway-brand"><span class="gateway-mark">M</span><div><strong>MediCart Pay</strong><small>Educational payment simulator</small></div><span class="lock-mark">▣ Encrypted demo</span></div>
  <div class="demo-notice"><strong>Demo environment only</strong><p>Do not enter real card, bank or wallet credentials. Choose an outcome below to demonstrate the payment workflow.</p></div>
 <?php if ($order['payment_status'] !== 'paid'): ?>
<form method="post" class="demo-payment-form">
    <button name="result" value="success" class="pay-success">
        <span>✓</span>
        <span>
            <strong>Simulate successful payment</strong>
            <small>Mark this order as paid</small>
        </span>
        <b>→</b>
    </button>

    <button name="result" value="failed" class="pay-failed">
        <span>×</span>
        <span>
            <strong>Simulate failed payment</strong>
            <small>Show a declined transaction</small>
        </span>
        <b>→</b>
    </button>
</form>
<?php else: ?>
    <div class="demo-notice">
        <strong>Payment completed</strong>
        <p>This order has already been paid. No further payment action is available.</p>
    </div>
<?php endif; ?>
  <div class="payment-footnote">🔒 No real payment is processed · No card details are collected</div>
 </section>
 <aside class="order-summary"><span class="eyebrow">ORDER SUMMARY</span><h2>Order #<?= $id ?></h2><div class="summary-line"><span>Customer</span><strong><?= e(current_user()['name']) ?></strong></div><div class="summary-line"><span>Email</span><strong><?= e(current_user()['email']) ?></strong></div><div class="summary-line"><span>Order date</span><strong><?= e((string)$order['created_at']) ?></strong></div><div class="summary-line"><span>Order status</span><strong><?= e(ucfirst((string)$order['status'])) ?></strong></div><div class="summary-line"><span>Payment status</span><strong><?= e(ucfirst((string)$order['payment_status'])) ?></strong></div><div class="summary-total"><span>Total to pay</span><strong><?= money((float)$order['total']) ?></strong></div>
  <!-- <div class="student-credit"><span>PROJECT PRESENTED BY</span><strong>Rinju Pokhrel <small>Roll No. 54</small></strong><strong>Harina Kumari Khati <small>Roll No. 49</small></strong><small>CSC 381 · E-Commerce</small></div> -->
 </aside>
</div>
<?php require 'includes/footer.php'; ?>
