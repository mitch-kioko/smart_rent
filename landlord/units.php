<?php
require '../config/db.php';
require '../includes/auth_check.php';
require_role(['Landlord']);

$units = $pdo->query('SELECT * FROM units ORDER BY unit_number')->fetchAll();

include '../includes/header.php';

?>
<h2>Units</h2>
<?php if (isset($_GET['saved'])): ?>
    <p class="msg-success">Unit saved.</p>
<?php endif; ?>
<p><a class="btn" href="/smart_rent/landlord/unit_form.php">+ Add unit</a></p>

<div class="table-wrap">
<table>
    <tr>
        <th>Unit</th><th>Description</th><th>Rent per month</th><th>Status</th><th></th>
    </tr>
    <?php foreach ($units as $u): ?>
    <tr>
        <td><?php echo htmlspecialchars($u['unit_number']); ?></td>
        <td><?php echo htmlspecialchars($u['description'] ?? '-'); ?></td>
        <td><?php echo number_format($u['rent_per_month'], 2); ?></td>
        <td><?php echo htmlspecialchars($u['status']); ?></td>
        <td><a href="/smart_rent/landlord/unit_form.php?id=<?php echo (int)$u['unit_id']; ?>">Edit</a></td>
    </tr>
    
    <?php endforeach; ?>
</table>
</div>
<?php include '../includes/footer.php'; ?>