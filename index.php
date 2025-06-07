<?php
session_start(); // Start session for auth later
require_once 'config.php';
require_once 'src/includes/functions.php';

// Basic router logic will go here eventually
// For now, just a simple page

include 'templates/partials/header.php';
?>

<div class="container">
    <h1>Welcome to the Invoicing System</h1>
    <p>This is the main entry point. More features coming soon!</p>
</div>

<?php
include 'templates/partials/footer.php';
?>
