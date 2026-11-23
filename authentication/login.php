<?php
session_start();
require '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] !== 'Active') {
            $error = 'This account is inactive. Contact the landlord.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'Tenant') {
                header('Location: /smartrent/tenant/dashboard.php');
            } else {
                header('Location: /smartrent/landlord/dashboard.php');
            }
            exit;
        }
    } else {
        $error = 'Invalid email or password.';
    }
}

include '../includes/header.php';

?>
<h2>Login</h2>

<?php if ($error): ?>
    <p style="color:#b00020;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="post" action="">
    <p><label>Email<br><input type="email" name="email" required></label></p>
    <p><label>Password<br><input type="password" name="password" required></label></p>
    <p><button type="submit">Log in</button></p>
</form>

<?php include '../includes/footer.php'; 
?>