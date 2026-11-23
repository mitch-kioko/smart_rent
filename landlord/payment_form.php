<?php
require '../config/db.php';
require '../includes/auth_check.php';
require '../includes/functions.php';
require_role(['Landlord', 'Caretaker']);

// Tenancies that can receive a payment: ongoing, or ended but still owing
$options = array_filter(get_tenancy_balances($pdo),
    fn($r) => $r['status'] === 'Ongoing' || $r['balance'] > 0);
usort($options, fn($a, $b) => strcmp($a['first_name'], $b['first_name']));
$valid_ids = array_column($options, 'tenancy_id');

$errors = [];
$tenancy_id = 0;
$amount = '';
$payment_date = date('Y-m-d');
$method = 'mpesa';
$reference = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenancy_id = (int)($_POST['tenancy_id'] ?? 0);
    $amount = trim($_POST['amount'] ?? '');
    $payment_date = trim($_POST['payment_date'] ?? '');
    $method = $_POST['payment_method'] ?? '';
    $reference = strtoupper(trim($_POST['reference'] ?? ''));

    if (!in_array($tenancy_id, $valid_ids)) {
        $errors[] = 'Choose a tenant from the list.';
    }
    if (!is_numeric($amount) || $amount <= 0 || $amount > 99999999) {
        $errors[] = 'Amount must be a number greater than zero (digits only, no commas).';
    }
    $d = DateTime::createFromFormat('Y-m-d', $payment_date);
    if (!$d || $d->format('Y-m-d') !== $payment_date) {
        $errors[] = 'Enter a valid payment date.';
    } elseif ($payment_date > date('Y-m-d')) {
        $errors[] = 'Payment date cannot be in the future.';
    }
    if (!in_array($method, ['cash', 'mpesa', 'bank'], true)) {
        $errors[] = 'Choose a payment method.';
    }
    if ($method === 'cash') {
        $reference = '';
    } elseif ($reference === '' || strlen($reference) > 50) {
        $errors[] = 'A reference (M-Pesa code or bank slip number) is required, max 50 characters.';
    }

    // The same M-Pesa / bank reference must not be recorded twice
    if (!$errors && $reference !== '') {
        $dup = $pdo->prepare('SELECT payment_id FROM payment WHERE reference = ?');
        $dup->execute([$reference]);
        if ($dup->fetch()) {
            $errors[] = 'That reference has already been recorded.';
        }
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // Next receipt number: highest existing number + 1, formatted RCT-0001
             $next = $pdo->query("SELECT COALESCE(MAX(CAST(SUBSTRING(receipt_number, 5) AS UNSIGNED)), 0) + 1
                     FROM payment
                     WHERE receipt_number REGEXP '^RCT-[0-9]{1,6}$'")->fetchColumn();
            $receipt = sprintf('RCT-%04d', $next);

                $sql = "INSERT INTO payment
                    (tenancy_id, amount, payment_date, payment_method, reference, receipt_number, recorded_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([
                $tenancy_id, $amount, $payment_date, $method,
                ($reference === '' ? null : $reference),
                $receipt, $_SESSION['user_id']
            ]);
            $pdo->commit();

            header('Location: /smart_rent/landlord/payments.php?saved=' . urlencode($receipt));
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
           $errors[] = 'Could not save the payment. Please try again.';
        }
    }
}

include '../includes/header.php';
?>
<h2>Record payment</h2>
<?php foreach ($errors as $e): ?>
    <p class="msg-error"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<?php if (!$options): ?>
    <p class="msg-error">No tenants with an active tenancy. Assign a unit to a tenant first.</p>
<?php else: ?>
<form method="post" action="" class="form-card">
    <p><label>Tenant<br>
        <select name="tenancy_id" required>
            <option value="">Select tenant</option>
            <?php foreach ($options as $o): ?>
                <option value="<?php echo (int)$o['tenancy_id']; ?>"
                    <?php echo $tenancy_id == $o['tenancy_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($o['first_name'] . ' ' . $o['last_name']
                        . ' - ' . $o['unit_number']
                        . ' (balance KES ' . number_format($o['balance'], 2) . ')'); ?>
                </option>
            <?php endforeach; ?>
            
        </select></label></p>
    <p><label>Amount (KES)<br>
        <input type="number" name="amount" min="1" step="0.01" required
               value="<?php echo htmlspecialchars($amount); ?>"></label></p>

    <p><label>Payment date<br>
        <input type="date" name="payment_date" required max="<?php echo date('Y-m-d'); ?>"
               value="<?php echo htmlspecialchars($payment_date); ?>"></label></p>

    <p><label>Method<br>
        <select name="payment_method">
            <option value="mpesa" <?php echo $method === 'mpesa' ? 'selected' : ''; ?>>M-Pesa</option>
            <option value="cash" <?php echo $method === 'cash' ? 'selected' : ''; ?>>Cash</option>
            <option value="bank" <?php echo $method === 'bank' ? 'selected' : ''; ?>>Bank</option>
        </select></label></p>

    <p><label>Reference (M-Pesa code or bank slip; leave blank for cash)<br>
        <input type="text" name="reference" maxlength="50"
               value="<?php echo htmlspecialchars($reference); ?>"></label></p>

    <p><button type="submit" class="btn">Save payment</button>
       <a href="/smart_rent/landlord/payments.php">Cancel</a></p>

</form>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>