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
$clients = [];
$page_error = '';

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT id, name, business_name, email, phone_number FROM clients WHERE admin_id = ? ORDER BY name ASC");
    $stmt->execute([$admin_id]);
    $clients = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Clients Page Error: " . $e->getMessage());
    $page_error = "Error fetching client data. Please try again later.";
}

include 'templates/partials/header.php';
?>

<div class="container">
    <h2>Manage Clients</h2>

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

    <p><a href="client_add.php">Add New Client</a></p>

    <?php if (empty($clients) && !$page_error): ?>
        <p>You have not added any clients yet.</p>
    <?php elseif (!empty($clients)): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Business Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['name']); ?></td>
                        <td><?php echo htmlspecialchars($client['business_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($client['email'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($client['phone_number'] ?? 'N/A'); ?></td>
                        <td>
                            <a href="client_edit.php?id=<?php echo $client['id']; ?>">Edit</a>
                            <a href="client_delete.php?id=<?php echo $client['id']; ?>" onclick="return confirm('Are you sure you want to delete this client?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'templates/partials/footer.php'; ?>
