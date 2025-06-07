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
$client_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$errors = [];
$client = null;

if (!$client_id) {
    $_SESSION['message'] = 'Invalid client ID.';
    header('Location: clients.php');
    exit;
}

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ? AND admin_id = ?");
    $stmt->execute([$client_id, $admin_id]);
    $client = $stmt->fetch();

    if (!$client) {
        $_SESSION['message'] = 'Client not found or you do not have permission to edit it.';
        header('Location: clients.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Client Edit (Fetch) Error: " . $e->getMessage());
    $_SESSION['message'] = 'Error fetching client data.';
    header('Location: clients.php');
    exit;
}

// Initialize form variables
$name = $client['name'];
$business_name = $client['business_name'];
$address = $client['address'];
$phone_number = $client['phone_number'];
$email = $client['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $business_name = trim($_POST['business_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Basic Validation
    if (empty($name)) {
        $errors[] = 'Client name is required.';
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format for client.';
    }
    // Add more validation as needed

    if (empty($errors)) {
        try {
            $pdo_update = get_db_connection();
            $update_stmt = $pdo_update->prepare("UPDATE clients SET name = ?, business_name = ?, address = ?, phone_number = ?, email = ? WHERE id = ? AND admin_id = ?");
            if ($update_stmt->execute([$name, $business_name, $address, $phone_number, $email, $client_id, $admin_id])) {
                $_SESSION['message'] = 'Client updated successfully!';
                header('Location: clients.php');
                exit;
            } else {
                $errors[] = 'Failed to update client. Please try again.';
            }
        } catch (PDOException $e) {
            error_log("Client Update Error: " . $e->getMessage());
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                 $errors[] = 'A client with this email might already exist for your account.';
            } else {
                $errors[] = 'An error occurred while updating the client. Please try again later.';
            }
        }
    }
}

include 'templates/partials/header.php';
?>

<div class="container">
    <h2>Edit Client: <?php echo htmlspecialchars($client['name']); ?></h2>

    <?php if (!empty($errors)): ?>
            <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="client_edit.php?id=<?php echo $client_id; ?>" method="POST">
        <div>
            <label for="name">Contact Name:</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>
        <div>
            <label for="business_name">Business Name (optional):</label>
            <input type="text" name="business_name" id="business_name" value="<?php echo htmlspecialchars($business_name); ?>">
        </div>
        <div>
            <label for="email">Email (optional):</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">
        </div>
        <div>
            <label for="phone_number">Phone Number (optional):</label>
            <input type="text" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>">
        </div>
        <div>
            <label for="address">Address (optional):</label>
            <textarea name="address" id="address"><?php echo htmlspecialchars($address); ?></textarea>
        </div>
        <button type="submit">Update Client</button>
        <a href="clients.php">Cancel</a>
    </form>
</div>

<?php include 'templates/partials/footer.php'; ?>
