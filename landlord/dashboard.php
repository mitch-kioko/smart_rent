<?php
require '../config/db.php';
require '../includes/auth_check.php';
require '../includes/functions.php';
require_role(['Landlord', 'Caretaker']);

$rows = get_tenancy_balances($pdo);
usort($rows, fn($a, $b) => $b['balance'] <=> $a['balance']);

$total_collected = $pdo->query('SELECT COALESCE(SUM(amount), 0) FROM payment')->fetchColumn();

$total_outstanding = 0;
$overdue_count = 0;
foreach ($rows as $r) {
    if ($r['balance'] > 0) {
        $total_outstanding += $r['balance'];
        $overdue_count++;
    }
}

include '../includes/header.php';
?>
<h2>Landlord Dashboard</h2>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>

<p>
    <a class="btn" href="/smart_rent/landlord/payment_form.php">Record payment</a>
    <a class="btn" href="/smart_rent/landlord/payments.php">View payments</a>
    <a class="btn" href="/smart_rent/landlord/defaulters.php">Defaulters</a>
    <a class="btn" href="/smart_rent/landlord/report.php">Monthly report</a>
    <?php if ($_SESSION['role'] === 'Landlord'): ?>
        <a class="btn" href="/smart_rent/landlord/units.php">Manage units</a>
        <a class="btn" href="/smart_rent/landlord/tenants.php">Manage tenants</a>
    <?php endif; ?>
</p>

<div class="stats">
    <div class="stat"><span>Total collected</span><strong>KES <?php echo number_format($total_collected, 2); ?></strong></div>
    <div class="stat"><span>Total outstanding</span><strong>KES <?php echo number_format($total_outstanding, 2); ?></strong></div>
    <div class="stat"><span>Tenants with arrears</span><strong><?php echo $overdue_count; ?></strong></div>
</div>

<h3>Tenant balances</h3>
<div class="table-wrap">
<table>
    <tr>
        <th>Tenant</th><th>Unit</th><th>Months due</th><th>Rent due</th>
        <th>Paid</th><th>Balance</th><th>Status</th>
    </tr>
    <?php foreach ($rows as $r): ?>
    <tr>
        <td><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></td>
        <td><?php echo htmlspecialchars($r['unit_number']); ?></td>
        <td><?php echo $r['months_due']; ?></td>
        <td><?php echo number_format($r['rent_due'], 2); ?></td>
        <td><?php echo number_format($r['total_paid'], 2); ?></td>
        <td><?php echo number_format($r['balance'], 2); ?></td>
        <td><span class="badge <?php echo $r['flag_class']; ?>"><?php echo htmlspecialchars($r['flag_label']); ?></span></td>
    </tr>
    <?php endforeach; ?>
</table>
</div>
<?php include '../includes/footer.php'; ?>