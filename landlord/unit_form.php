<?php
require '../config/db.php';
require '../includes/auth_check.php';
require_role(['Landlord']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];
$unit_number = '';
$description = '';
$rent = '';

// Edit mode: load the existing unit
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM units WHERE unit_id = ?');
    $stmt->execute([$id]);
    $unit = $stmt->fetch();
    if (!$unit) {
        http_response_code(404);
        die('Unit not found.');
    }
    $unit_number = $unit['unit_number'];
    $description = $unit['description'] ?? '';
    $rent = $unit['rent_per_month'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_number = trim($_POST['unit_number'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $rent = trim($_POST['rent_per_month'] ?? '');

    if ($unit_number === '' || strlen($unit_number) > 20) {
        $errors[] = 'Unit number is required (max 20 characters).';
    }
    if (strlen($description) > 255) {
        $errors[] = 'Description is too long (max 255 characters).';
    }
    if (!is_numeric($rent) || $rent <= 0) {
        $errors[] = 'Rent must be a number greater than zero (digits only, no commas).';
    }

    // Unit numbers must be unique
    if (!$errors) {
        $check = $pdo->prepare('SELECT unit_id FROM units WHERE unit_number = ? AND unit_id <> ?');
        $check->execute([$unit_number, $id]);
        if ($check->fetch()) {
            $errors[] = 'That unit number already exists.';
        }
    }

    if (!$errors) {
        $desc = ($description === '') ? null : $description;
        if ($id > 0) {
            $sql = 'UPDATE units SET unit_number = ?, description = ?, rent_per_month = ? WHERE unit_id = ?';
            $pdo->prepare($sql)->execute([$unit_number, $desc, $rent, $id]);
        } else {
            $sql = "INSERT INTO units (unit_number, description, rent_per_month, status)
                    VALUES (?, ?, ?, 'Vacant')";
            $pdo->prepare($sql)->execute([$unit_number, $desc, $rent]);
        }
        header('Location: /smart_rent/landlord/units.php?saved=1');
        exit;
    }
}

include '../includes/header.php';
?>
<h2><?php echo $id > 0 ? 'Edit unit' : 'Add unit'; ?></h2>

<?php foreach ($errors as $e): ?>
    <p class="msg-error"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post" action="" class="form-card">
    <p><label>Unit number<br>
        <input type="text" name="unit_number" maxlength="20" required
               value="<?php echo htmlspecialchars($unit_number); ?>"></label></p>
    <p><label>Description (optional)<br>
        <input type="text" name="description" maxlength="255"
               value="<?php echo htmlspecialchars($description); ?>"></label></p>
    <p><label>Rent per month (KES)<br>
        <input type="number" name="rent_per_month" min="1" step="0.01" required
               value="<?php echo htmlspecialchars($rent); ?>"></label></p>
    <p><button type="submit" class="btn">Save</button>
       <a href="/smart_rent/landlord/units.php">Cancel</a></p>
</form>
<?php include '../includes/footer.php'; ?>