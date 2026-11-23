<?php
require 'config/db.php';
include 'includes/header.php';

$count = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
?>
<h2>Welcome to SmartRent</h2>
<p>Database connected. Users in the system: <?php echo $count; ?></p>
<?php include 'includes/footer.php'; 
?>