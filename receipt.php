<?php
require 'config/db.php';
require 'includes/auth_check.php';
require 'includes/functions.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT p.*, t.tenant_id, t.rent_per_month,
                              tu.first_name AS t_first, tu.last_name AS t_last, tu.phone_number,
                              un.unit_number,
                              rb.first_name AS r_first, rb.last_name AS r_last
                       FROM payment p
                       JOIN tenancies t ON t.tenancy_id = p.tenancy_id
                       JOIN users tu ON tu.user_id = t.tenant_id
                       JOIN units un ON un.unit_id = t.unit_id
                       JOIN users rb ON rb.user_id = p.recorded_by
                       WHERE p.payment_id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

// Tenants may only see their own receipts. Others get "not found",
// so the page doesn't reveal which receipt numbers exist.
$allowed = $p && (
    in_array($_SESSION['role'], ['Landlord', 'Caretaker'], true)
    || ($_SESSION['role'] === 'Tenant' && (int)$p['tenant_id'] === (int)$_SESSION['user_id'])
);
if (!$allowed) {
    http_response_code(404);
    die('Receipt not found.');
}

// Current balance for this tenancy
$balance = 0;
foreach (get_tenancy_balances($pdo) as $r) {
    if ((int)$r['tenancy_id'] === (int)$p['tenancy_id']) {
        $balance = $r['balance'];
        break;
    }
}

$back = ($_SESSION['role'] === 'Tenant')
    ? '/smart_rent/tenant/dashboard.php'
    : '/smart_rent/landlord/payments.php';

$methods = ['cash' => 'Cash', 'mpesa' => 'M-Pesa', 'bank' => 'Bank'];

include 'includes/header.php';
?>
<div class="receipt">
    <h2>SmartRent Payment Receipt</h2>
    <p class="receipt-no">Receipt No: <strong><?php echo htmlspecialchars($p['receipt_number']); ?></strong></p>

    <table class="receipt-table">
        <tr><th>Date paid</th><td><?php echo htmlspecialchars($p['payment_date']); ?></td></tr>
        <tr><th>Received from</th><td><?php echo htmlspecialchars($p['t_first'] . ' ' . $p['t_last']); ?></td></tr>
        <tr><th>Unit</th><td><?php echo htmlspecialchars($p['unit_number']); ?></td></tr>
        <tr><th>Amount</th><td><strong>KES <?php echo number_format($p['amount'], 2); ?></strong></td></tr>
        <tr><th>Method</th><td><?php echo htmlspecialchars($methods[$p['payment_method']] ?? $p['payment_method']); ?></td></tr>
        <tr><th>Reference</th><td><?php echo htmlspecialchars($p['reference'] ?? '-'); ?></td></tr>
        <tr><th>Recorded by</th><td><?php echo htmlspecialchars($p['r_first'] . ' ' . $p['r_last']); ?></td></tr>
        <tr><th>Recorded on</th><td><?php echo htmlspecialchars($p['created_on']); ?></td></tr>
        <tr><th>Balance as at <?php echo date('Y-m-d'); ?></th>
            <td>KES <?php echo number_format($balance, 2); ?>
                <?php echo $balance > 0 ? '(owing)' : ($balance < 0 ? '(paid in advance)' : '(paid up)'); ?></td></tr>
    </table>

    <p class="receipt-note">Thank you. Keep this receipt as proof of payment.</p>

    <p class="no-print">
        <button type="button" onclick="window.print()">Print receipt</button>
        <a href="<?php echo $back; ?>">Back</a>
    </p>
</div>
<?php include 'includes/footer.php'; ?>