<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartRent</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
 
    <div class="register-container">
        <h2>Register</h2>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="reg-firstname">First Name</label>
                <input type="text" id="reg-firstname" name="reg-firstname" required>
            </div>
            <div class="form-group">
                <label for="reg-lastname">Last Name</label>
                <input type="text" id="reg-lastname" name="reg-lastname" required>
            </div>
            <div class="form-group">
                <label for="reg-email">Email</label>
                <input type="email" id="reg-email" name="reg-email" required>
            </div>
            <div class="form-group">
                <label for="reg-phonenumber">Phone Number</label>
                <input type="tel" id="reg-phonenumber" name="reg-phonenumber" required>
            </div>
            <div class="form-group">
                <label for="reg-password">Password</label>
                <input type="password" id="reg-password" name="reg-password" required>
            </div>
            <div class="form-group">
                <label for="reg-role">Role</label>
                <select id="reg-role" name="reg-role" required>
                    <option value="Tenant">Tenant</option>
                    <option value="Landlord">Landlord</option>
                    <option value="Caretaker">Caretaker</option>
                </select>
            </div>
            <div class="form-group">
                <label for="reg-status">Status</label>
                <select id="reg-status" name="reg-status" required>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <button type="submit">Register</button>
            already have an account? <a href="login.php">Login here</a>
        </form>
    </div>
</body>
</html>
 