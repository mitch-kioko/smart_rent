<?php
require '../config/db.php';
require '../includes/auth_check.php';
require_role(['Landlord']);

$sql = "SELECT u.user_id, u.first_name, u.last_name, u.phone_number, u.email, u.status,
               t.tenancy_id, un.unit_number
        FROM users u
        LEFT JOIN tenancies t ON t.tenant_id = u.user_id AND t.status = 'Ongoing'
        LEFT JOIN units un ON un.unit_id = t.unit_id
        WHERE u.role = 'Tenant'
        ORDER BY u.first_name, u.last_name";
$tenants = $pdo->query($sql)->fetchAll();

include '../includes/header.php';
?>
<h2>Tenants</h2>
<?php if (isset($_GET['saved'])): ?>
    <p class="msg-success"><?php echo htmlspecialchars($_GET['saved']); ?></p>
<?php endif; ?>
<p>
    <a class="btn" href="/smart_rent/landlord/tenant_form.php">+ Register tenant</a>
    <a class="btn" href="/smart_rent/landlord/tenancy_form.php">+ Assign unit</a>
</p>

<div class="table-wrap">
<table>
    <tr>
        <th>Name</th><th>Phone</th><th>Email</th><th>Account</th><th>Current unit</th><th></th>
    </tr>
    <?php foreach ($tenants as $t): ?>
    <tr>
        <td><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></td>
        <td><?php echo htmlspecialchars($t['phone_number']); ?></td>
        <td><?php echo htmlspecialchars($t['email']); ?></td>
        <td><?php echo htmlspecialchars($t['status']); ?></td>
        <td><?php echo $t['unit_number'] ? htmlspecialchars($t['unit_number']) : 'None'; ?></td>
        <td>
            <?php if ($t['tenancy_id']): ?>
                <a href="/smart_rent/landlord/tenancy_end.php?id=<?php echo (int)$t['tenancy_id']; ?>">End tenancy</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</div>
<?php include '../includes/footer.php'; ?>