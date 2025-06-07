<?php
session_start();
require_once 'config.php';
require_once 'src/includes/db.php';
require_once 'src/includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// For CSRF protection, it's better to use POST for delete actions.
// However, the current link is GET with a JS confirm.
// For robustness, check request method if you enforce POST.
// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//    $_SESSION['message'] = 'Invalid request method.';
//    header('Location: companies.php');
//    exit;
// }
// And add a CSRF token to the delete link/form.

$admin_id = $_SESSION['user_id'];
$company_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Or INPUT_POST if changed

if (!$company_id) {
    $_SESSION['message'] = 'Invalid company ID.';
    header('Location: companies.php');
    exit;
}

try {
    $pdo = get_db_connection();

    // First, verify the company belongs to the admin
    $stmt_check = $pdo->prepare("SELECT id FROM companies WHERE id = ? AND admin_id = ?");
    $stmt_check->execute([$company_id, $admin_id]);
    if (!$stmt_check->fetch()) {
        $_SESSION['message'] = 'Company not found or you do not have permission to delete it.';
        header('Location: companies.php');
        exit;
    }

    // Proceed with deletion
    $stmt_delete = $pdo->prepare("DELETE FROM companies WHERE id = ? AND admin_id = ?");
    if ($stmt_delete->execute([$company_id, $admin_id])) {
        $_SESSION['message'] = 'Company deleted successfully!';
    } else {
        $_SESSION['message'] = 'Failed to delete company. Please try again.';
    }
} catch (PDOException $e) {
    error_log("Company Delete Error: " . $e->getMessage());
    $_SESSION['message'] = 'An error occurred while deleting the company. Please try again later.';
}

header('Location: companies.php');
exit;
?>
