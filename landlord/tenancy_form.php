<?php
require '../config/db.php';
require '../includes/auth_check.php';
require_role(['Landlord']);

$errors = [];

// Active tenants with no ongoing tenancy
$tenants = $pdo->query(
    "SELECT user_id, first_name, last_name FROM users
     WHERE role = 'Tenant' AND status = 'Active'
       AND NOT EXISTS (SELECT 1 FROM tenancies t
                       WHERE t.tenant_id = users.user_id AND t.status = 'Ongoing')
     ORDER BY first_name"
)->fetchAll();

$units = $pdo->query("SELECT unit_id, unit_number, rent_per_month FROM units
                      WHERE status = 'Vacant' ORDER BY unit_number")->fetchAll();

$tenant_id = $unit_id = 0;
$start_date = date('Y-m-d');
$rent = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id = (int)($_POST['tenant_id'] ?? 0);
    $unit_id = (int)($_POST['unit_id'] ?? 0);
    $start_date = trim($_POST['start_date'] ?? '');
    $rent = trim($_POST['rent_per_month'] ?? '');

    $d = DateTime::createFromFormat('Y-m-d', $start_date);
    if (!$d || $d->format('Y-m-d') !== $start_date) $errors[] = 'Enter a valid start date.';
    if ($rent !== '' && (!is_numeric($rent) || $rent <= 0)) {
        $errors[] = 'Rent must be a number greater than zero, or left blank to use the unit rent.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // Re-check inside the transaction and lock the rows
            $u = $pdo->prepare("SELECT rent_per_month FROM units WHERE unit_id = ? AND status = 'Vacant' FOR UPDATE");
            $u->execute([$unit_id]);
            $unit = $u->fetch();

            $t = $pdo->prepare("SELECT user_id FROM users
                                WHERE user_id = ? AND role = 'Tenant' AND status = 'Active'
                                  AND NOT EXISTS (SELECT 1 FROM tenancies
                                                  WHERE tenant_id = users.user_id AND status = 'Ongoing')");
            $t->execute([$tenant_id]);

            if (!$unit || !$t->fetch()) {
                $pdo->rollBack();
                $errors[] = 'Choose an available tenant and a vacant unit.';
            } else {
                $final_rent = ($rent === '') ? $unit['rent_per_month'] : $rent;
                $pdo->prepare("INSERT INTO tenancies (tenant_id, unit_id, rent_per_month, start_date, end_date, status)
                               VALUES (?, ?, ?, ?, NULL, 'Ongoing')")
                    ->execute([$tenant_id, $unit_id, $final_rent, $start_date]);
                $pdo->prepare("UPDATE units SET status = 'Occupied' WHERE unit_id = ?")->execute([$unit_id]);
                $pdo->commit();
                header('Location: /smart_rent/landlord/tenants.php?saved=' . urlencode('Unit assigned.'));
                exit;
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Could not save the tenancy. Please try again.';
        }
    }
}

include '../includes/header.php';
?>
<h2>Assign unit to tenant</h2>
<?php foreach ($errors as $e): ?>
    <p class="msg-error"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<?php if (!$tenants || !$units): ?>
    <p class="msg-error">
        <?php echo !$tenants ? 'No active tenants without a unit. Register a tenant first. ' : ''; ?>
        <?php echo !$units ? 'No vacant units. Add a unit first.' : ''; ?>
    </p>
<?php else: ?>
<form method="post" action="" class="form-card">
    <p><label>Tenant<br>
        <select name="tenant_id" required>
            <option value="">Select tenant</option>
            <?php foreach ($tenants as $t): ?>
                <option value="<?php echo (int)$t['user_id']; ?>" <?php echo $tenant_id == $t['user_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?>
                </option>
            <?php endforeach; ?>
        </select></label></p>
    <p><label>Vacant unit<br>
        <select name="unit_id" required>
            <option value="">Select unit</option>
            <?php foreach ($units as $un): ?>
                <option value="<?php echo (int)$un['unit_id']; ?>" <?php echo $unit_id == $un['unit_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($un['unit_number']) . ' (KES ' . number_format($un['rent_per_month'], 2) . ')'; ?>
                </option>
            <?php endforeach; ?>
        </select></label></p>
    <p><label>Start date<br>
        <input type="date" name="start_date" required value="<?php echo htmlspecialchars($start_date); ?>"></label></p>
    <p><label>Agreed rent (leave blank to use the unit's rent)<br>
        <input type="number" name="rent_per_month" min="1" step="0.01"
               value="<?php echo htmlspecialchars($rent); ?>"></label></p>
    <p><button type="submit" class="btn">Assign</button>
       <a href="/smart_rent/landlord/tenants.php">Cancel</a></p>
</form>
<?php endif; ?>
<?php include '../includes/footer.php'; 
?>