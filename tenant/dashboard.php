<?php
require '../config/db.php';
require '../includes/auth_check.php';
require '../includes/functions.php';
require_role(['Tenant']);

$uid = (int)$_SESSION['user_id'];

// Only this tenant's tenancies: the filter is applied after the shared calculation
$mine = array_values(array_filter(
    get_tenancy_balances($pdo),
    fn($r) => (int)$r['tenant_id'] === $uid
));

// Newest tenancy first
usort($mine, fn($a, $b) => strcmp($b['start_date'], $a['start_date']));

// This tenant's payments, newest first
$stmt = $pdo->prepare("SELECT p.payment_id, p.amount, p.payment_date, p.payment_method,
                              p.reference, p.receipt_number, un.unit_number
                       FROM payment p
                       JOIN tenancies t ON t.tenancy_id = p.tenancy_id
                       JOIN units un ON un.unit_id = t.unit_id
                       WHERE t.tenant_id = ?
                       ORDER BY p.payment_date DESC, p.payment_id DESC");
$stmt->execute([$uid]);
$payments = $stmt->fetchAll();

$labels = ['cash' => 'Cash', 'mpesa' => 'M-Pesa', 'bank' => 'Bank'];

include '../includes/header.php';
?>
<h2>My Account</h2>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></p>

<?php if (!$mine): ?>
    <p class="msg-error">You have no tenancy on record yet. Please contact the landlord.</p>
<?php else: ?>
    <?php foreach ($mine as $r): ?>
        
    <div class="stats">
        <div class="stat"><span>Unit</span><strong><?php echo htmlspecialchars($r['unit_number']); ?></strong></div>
        <div class="stat"><span>Monthly rent</span><strong>KES <?php echo number_format($r['rent_per_month'], 2); ?></strong></div>
        <div class="stat"><span>Total paid</span><strong>KES <?php echo number_format($r['total_paid'], 2); ?></strong></div>
        <div class="stat"><span>Balance</span>
            <strong>KES <?php echo number_format($r['balance'], 2); ?></strong>
            <span class="badge <?php echo $r['flag_class']; ?>"><?php echo htmlspecialchars($r['flag_label']); ?></span></div>
    </div>
    <p>Tenancy: <?php echo htmlspecialchars($r['start_date']); ?> to
       <?php echo $r['end_date'] ? htmlspecialchars($r['end_date']) : 'present'; ?>
       (<?php echo $r['months_due']; ?> months of rent, KES <?php echo number_format($r['rent_due'], 2); ?> due in total)</p>
    <?php endforeach; ?>
<?php endif; ?>

<h3>Payment history</h3>
<?php if (!$payments): ?>
    <p>No payments have been recorded yet.</p>
<?php else: ?>

<div class="table-wrap">
<table>
    <tr><th>Date</th><th>Receipt</th><th>Unit</th><th>Method</th><th>Reference</th><th>Amount</th><th></th></tr>
    <?php foreach ($payments as $p): ?>
    <tr>
        <td><?php echo htmlspecialchars($p['payment_date']); ?></td>
        <td><?php echo htmlspecialchars($p['receipt_number']); ?></td>
        <td><?php echo htmlspecialchars($p['unit_number']); ?></td>
        <td><?php echo $labels[$p['payment_method']] ?? htmlspecialchars($p['payment_method']); ?></td>
        <td><?php echo htmlspecialchars($p['reference'] ?? '-'); ?></td>
        <td><?php echo number_format($p['amount'], 2); ?></td>
        <td><a href="/smart_rent/receipt.php?id=<?php echo (int)$p['payment_id']; ?>">View / print</a></td>
    </tr>
    <?php endforeach; ?>
</table>
</div>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>