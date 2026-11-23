<?php
require '../config/db.php';
require '../includes/auth_check.php';
require '../includes/functions.php';
require_role(['Landlord', 'Caretaker']);

$defaulters = array_filter(get_tenancy_balances($pdo), fn($r) => $r['balance'] > 0);
usort($defaulters, fn($a, $b) => $b['balance'] <=> $a['balance']);
$total = array_sum(array_column($defaulters, 'balance'));

include '../includes/header.php';
?>
<h2>Defaulters</h2>
<p>Tenants with an unpaid balance, largest first.</p>

<?php if (!$defaulters): ?>
    <p class="msg-success">No arrears. Every tenant is paid up.</p>
<?php else: ?>
<div class="table-wrap">
<table>
    <tr>
        <th>#</th><th>Tenant</th><th>Phone</th><th>Unit</th>
        <th>Months behind</th><th>Balance</th><th>Status</th>
    </tr>
    <?php foreach ($defaulters as $i => $r): ?>

    <tr>
        <td><?php echo $i + 1; ?></td>
        <td><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></td>
        <td><?php echo htmlspecialchars($r['phone_number']); ?></td>
        <td><?php echo htmlspecialchars($r['unit_number']); ?></td>
        <td><?php echo $r['months_behind']; ?></td>
        <td><?php echo number_format($r['balance'], 2); ?></td>
        <td><span class="badge <?php echo $r['flag_class']; ?>"><?php echo htmlspecialchars($r['flag_label']); ?></span></td>
    </tr>

    <?php endforeach; ?>
    <tr>
        <th colspan="5">Total owed</th>
        <th colspan="2">KES <?php echo number_format($total, 2); ?></th>
    </tr>

</table>
</div>
<?php endif; ?>
<p><a href="/smart_rent/landlord/dashboard.php">Back to dashboard</a></p>
<?php include '../includes/footer.php'; ?>