<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartRent</title>
    <link rel="stylesheet" href="/smart_rent/assets/css/style.css">

</head>
<body>
<header class="site-header">
    
    <h1>SmartRent</h1>
    <nav>
        <a href="/smart_rent/index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/smart_rent/authentication/logout.php">Logout</a>
        <?php else: ?>
            <a href="/smart_rent/authentication/login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container"></main>