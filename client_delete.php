<?php
session_start();
require_once 'config.php';
require_once 'src/includes/db.php';
require_once 'src/includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$client_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Or INPUT_POST if using POST for delete

if (!$client_id) {
    $_SESSION['message'] = 'Invalid client ID.';
    header('Location: clients.php');
    exit;
}

try {
    $pdo = get_db_connection();

    // Verify the client belongs to the admin before deleting
    $stmt_check = $pdo->prepare("SELECT id FROM clients WHERE id = ? AND admin_id = ?");
    $stmt_check->execute([$client_id, $admin_id]);
    if (!$stmt_check->fetch()) {
        $_SESSION['message'] = 'Client not found or you do not have permission to delete it.';
        header('Location: clients.php');
        exit;
    }

    // Proceed with deletion
    $stmt_delete = $pdo->prepare("DELETE FROM clients WHERE id = ? AND admin_id = ?");
    if ($stmt_delete->execute([$client_id, $admin_id])) {
        $_SESSION['message'] = 'Client deleted successfully!';
    } else {
        $_SESSION['message'] = 'Failed to delete client. Please try again.';
    }
} catch (PDOException $e) {
    error_log("Client Delete Error: " . $e->getMessage());
    $_SESSION['message'] = 'An error occurred while deleting the client. Please try again later.';
}

header('Location: clients.php');
exit;
?>
