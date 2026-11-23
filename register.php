<?php
// Registration logic: replace the PHP block at the top of register.php with this
session_start();
require_once 'db_conn.php';

$error = "";

// Roles a person may choose when registering 
$allowed_roles = ["Tenant", "Landlord", "Caretaker"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST["reg-firstname"] ?? "");
    $lastname  = trim($_POST["reg-lastname"] ?? "");
    $phone     = trim($_POST["reg-phonenumber"] ?? "");
    $email     = trim($_POST["reg-email"] ?? "");
    $role      = $_POST["reg-role"] ?? "";
    $pass      = $_POST["reg-password"] ?? "";
    $status    = "Active"; // set by the server, not the form. Change to match your column's values.


    if ($firstname === "" || $lastname === "" || $phone === "" || $email === "" || $pass === "") {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (!in_array($role, $allowed_roles, true)) {
        $error = "Please choose a valid role.";
    } else {
        $password = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users (first_name, last_name, status, phone_number, email, role, password)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssssss", $firstname, $lastname, $status, $phone, $email, $role, $password);

        try {
            $stmt->execute();
            header("Location: login.php");
            exit;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "That email is already registered.";
            } else {
                
                $error = "Registration failed: " . $e->getMessage();
            }
        }
        $stmt->close();
    }
}
?>
<?php include 'register_form.php'; ?>
?>