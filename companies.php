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
$companies = [];
$page_error = '';

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT id, name, email, phone, currency FROM companies WHERE admin_id = ? ORDER BY name ASC");
    $stmt->execute([$admin_id]);
    $companies = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Companies Page Error: " . $e->getMessage());
    $page_error = "Error fetching company data. Please try again later.";
}

include 'templates/partials/header.php';
?>

<div class="container">
    <h2>Manage Companies</h2>

    <?php if (isset($_SESSION['message'])): ?>
            <div class="success">
            <p><?php echo htmlspecialchars($_SESSION['message']); ?></p>
            <?php unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <?php if ($page_error): ?>
            <div class="errors">
            <p><?php echo htmlspecialchars($page_error); ?></p>
        </div>
    <?php endif; ?>

    <p><a href="company_add.php">Add New Company</a></p>

    <?php if (empty($companies) && !$page_error): ?>
        <p>You have not added any companies yet.</p>
    <?php elseif (!empty($companies)): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Currency</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $company): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($company['name']); ?></td>
                        <td><?php echo htmlspecialchars($company['email'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($company['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($company['currency']); ?></td>
                        <td>
                            <a href="company_edit.php?id=<?php echo $company['id']; ?>">Edit</a>
                            <a href="company_delete.php?id=<?php echo $company['id']; ?>" onclick="return confirm('Are you sure you want to delete this company?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'templates/partials/footer.php'; ?>
