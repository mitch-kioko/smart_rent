<?php
require '../config/db.php';
require '../includes/auth_check.php';
require_role(['Landlord']);

$errors = [];
$first = $last = $phone = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($first === '' || strlen($first) > 30) $errors[] = 'First name is required (max 30 characters).';
    if ($last === '' || strlen($last) > 30) $errors[] = 'Last name is required (max 30 characters).';
    if (!preg_match('/^0[17][0-9]{8}$/', $phone)) {
        $errors[] = 'Phone must be 10 digits starting with 07 or 01.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

    if (!$errors) {
        $check = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) $errors[] = 'That email is already registered.';
    }

    if (!$errors) {
        $sql = "INSERT INTO users (first_name, last_name, phone_number, email, password, role, status)
                VALUES (?, ?, ?, ?, ?, 'Tenant', 'Active')";
        $pdo->prepare($sql)->execute([$first, $last, $phone, $email, password_hash($password, PASSWORD_DEFAULT)]);
        header('Location: /smart_rent/landlord/tenants.php?saved=' . urlencode('Tenant registered.'));
        exit;
    }
}

include '../includes/header.php';
?>
<h2>Register tenant</h2>
<?php foreach ($errors as $e): ?>
    <p class="msg-error"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post" action="" class="form-card">
    <p><label>First name<br><input type="text" name="first_name" maxlength="30" required
        value="<?php echo htmlspecialchars($first); ?>"></label></p>
    <p><label>Last name<br><input type="text" name="last_name" maxlength="30" required
        value="<?php echo htmlspecialchars($last); ?>"></label></p>
    <p><label>Phone (e.g. 0712345678)<br><input type="text" name="phone_number" maxlength="10" required
        value="<?php echo htmlspecialchars($phone); ?>"></label></p>
    <p><label>Email<br><input type="email" name="email" maxlength="100" required
        value="<?php echo htmlspecialchars($email); ?>"></label></p>
    <p><label>Temporary password (min 8 characters)<br><input type="password" name="password" required></label></p>
    <p><button type="submit" class="btn">Register</button>
       <a href="/smart_rent/landlord/tenants.php">Cancel</a></p>
</form>
<?php include '../includes/footer.php'; ?>