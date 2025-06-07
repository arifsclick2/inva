<?php
session_start();
require_once 'config.php';
require_once 'src/includes/functions.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'templates/partials/header.php';
?>

<div class="container">
    <h2>Admin Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!</p>
    <p>This is your dashboard. More features will be added here.</p>
    <ul>
        <li><a href="companies.php">Manage Companies</a></li>
        <li><a href="clients.php">Manage Clients</a></li>
        <li><a href="products.php">Manage Products</a> (Coming Soon)</li>
        <li><a href="invoices.php">Manage Invoices</a> (Coming Soon)</li>
    </ul>
</div>

<?php include 'templates/partials/footer.php'; ?>
