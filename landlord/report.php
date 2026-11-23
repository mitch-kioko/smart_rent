<?php
require '../config/db.php';
require '../includes/auth_check.php';
require_role(['Landlord', 'Caretaker']);

// Month comes from ?month=YYYY-MM, default is the current month
$month = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
    $month = date('Y-m');
}
$first = $month . '-01';
$last = date('Y-m-t', strtotime($first));

// All payments received in the month
$stmt = $pdo->prepare("SELECT p.payment_id, p.amount, p.payment_date, p.payment_method,
                              p.reference, p.receipt_number,
                              tu.first_name, tu.last_name, un.unit_number
                       FROM payment p
                       JOIN tenancies t ON t.tenancy_id = p.tenancy_id
                       JOIN users tu ON tu.user_id = t.tenant_id
                       JOIN units un ON un.unit_id = t.unit_id
                       WHERE p.payment_date BETWEEN ? AND ?
                       ORDER BY p.payment_date, p.payment_id");
$stmt->execute([$first, $last]);
$payments = $stmt->fetchAll();

// Totals overall and by method
$total = 0;
$by_method = ['cash' => 0, 'mpesa' => 0, 'bank' => 0];
foreach ($payments as $p) {
    $total += $p['amount'];
    $by_method[$p['payment_method']] += $p['amount'];
}

// Rent that fell due in the month: tenancies that were running during it
$stmt = $pdo->prepare("SELECT COALESCE(SUM(rent_per_month), 0) FROM tenancies
                       WHERE start_date <= ?
                         AND (end_date IS NULL OR end_date >= ?)");
$stmt->execute([$last, $first]);
$expected = $stmt->fetchColumn();

$labels = ['cash' => 'Cash', 'mpesa' => 'M-Pesa', 'bank' => 'Bank'];
$month_name = date('F Y', strtotime($first));

include '../includes/header.php';
?>
<h2>Monthly collection report</h2>

<form method="get" action="" class="no-print">
    <p><label>Month<br>
        <input type="month" name="month" value="<?php echo htmlspecialchars($month); ?>" required></label>
       <button type="submit" class="btn">Show</button></p>
</form>

<h3><?php echo htmlspecialchars($month_name); ?></h3>

<div class="stats">
    <div class="stat"><span>Collected</span><strong>KES <?php echo number_format($total, 2); ?></strong></div>
    <div class="stat"><span>Rent due this month</span><strong>KES <?php echo number_format($expected, 2); ?></strong></div>
    <div class="stat"><span>Payments recorded</span><strong><?php echo count($payments); ?></strong></div>
</div>

<h3>By payment method</h3>
<div class="table-wrap">
<table>
    <tr><th>Method</th><th>Amount</th></tr>
    <?php foreach ($by_method as $m => $amt): ?>
    <tr><td><?php echo $labels[$m]; ?></td><td>KES <?php echo number_format($amt, 2); ?></td></tr>
    <?php endforeach; ?>
</table>
</div>

<h3>Payments</h3>
<?php if (!$payments): ?>
    <p>No payments were recorded in <?php echo htmlspecialchars($month_name); ?>.</p>
<?php else: ?>
<div class="table-wrap">
<table>
    <tr>
        <th>Date</th><th>Receipt</th><th>Tenant</th><th>Unit</th>
        <th>Method</th><th>Reference</th><th>Amount</th>
    </tr>
    <?php foreach ($payments as $p): ?>
    <tr>
        <td><?php echo htmlspecialchars($p['payment_date']); ?></td>
        <td><a href="/smart_rent/receipt.php?id=<?php echo (int)$p['payment_id']; ?>">
            <?php echo htmlspecialchars($p['receipt_number']); ?></a></td>
        <td><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></td>
        <td><?php echo htmlspecialchars($p['unit_number']); ?></td>
        <td><?php echo $labels[$p['payment_method']]; ?></td>
        <td><?php echo htmlspecialchars($p['reference'] ?? '-'); ?></td>
        <td><?php echo number_format($p['amount'], 2); ?></td>
    </tr>
    <?php endforeach; ?>
    <tr><th colspan="6">Total</th><th>KES <?php echo number_format($total, 2); ?></th></tr>
</table>
</div>
<?php endif; ?>

<p class="no-print">
    <button type="button" onclick="window.print()">Print report</button>
    <a href="/smart_rent/landlord/dashboard.php">Back to dashboard</a>
</p>
<?php include '../includes/footer.php'; ?>