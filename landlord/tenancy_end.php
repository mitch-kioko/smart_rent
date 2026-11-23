<?php
require '../config/db.php';
require '../includes/auth_check.php';
require_role(['Landlord']);

$id = (int)($_GET['id'] ?? $_POST['tenancy_id'] ?? 0);

$stmt = $pdo->prepare("SELECT t.*, u.first_name, u.last_name, un.unit_number
                       FROM tenancies t
                       JOIN users u ON u.user_id = t.tenant_id
                       JOIN units un ON un.unit_id = t.unit_id
                       WHERE t.tenancy_id = ? AND t.status = 'Ongoing'");
$stmt->execute([$id]);
$tn = $stmt->fetch();
if (!$tn) {
    http_response_code(404);
    die('Ongoing tenancy not found.');
}

$errors = [];
$end_date = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $end_date = trim($_POST['end_date'] ?? '');
    $d = DateTime::createFromFormat('Y-m-d', $end_date);
    if (!$d || $d->format('Y-m-d') !== $end_date) {
        $errors[] = 'Enter a valid end date.';
    } elseif ($end_date < $tn['start_date']) {
        $errors[] = 'End date cannot be before the start date (' . $tn['start_date'] . ').';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE tenancies SET status = 'Ended', end_date = ? WHERE tenancy_id = ?")
                ->execute([$end_date, $id]);
            $pdo->prepare("UPDATE units SET status = 'Vacant' WHERE unit_id = ?")
                ->execute([$tn['unit_id']]);
            $pdo->commit();
            header('Location: /smart_rent/landlord/tenants.php?saved=' . urlencode('Tenancy ended.'));
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Could not end the tenancy. Please try again.';
        }
    }
}

include '../includes/header.php';
?>
<h2>End tenancy</h2>
<p><?php echo htmlspecialchars($tn['first_name'] . ' ' . $tn['last_name']); ?>,
   unit <?php echo htmlspecialchars($tn['unit_number']); ?>,
   started <?php echo htmlspecialchars($tn['start_date']); ?>.</p>

<?php foreach ($errors as $e): ?>
    <p class="msg-error"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post" action="" class="form-card">
    <input type="hidden" name="tenancy_id" value="<?php echo (int)$id; ?>">
    <p><label>Move-out date<br>
        <input type="date" name="end_date" required min="<?php echo htmlspecialchars($tn['start_date']); ?>"
               value="<?php echo htmlspecialchars($end_date); ?>"></label></p>
    <p><button type="submit" class="btn">End tenancy</button>
       <a href="/smart_rent/landlord/tenants.php">Cancel</a></p>
</form>
<?php include '../includes/footer.php'; 
?>