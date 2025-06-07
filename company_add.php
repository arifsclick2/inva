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
$errors = [];
$company_name = '';
$logo_url = '';
$address = '';
$phone = '';
$email = '';
$currency = 'MYR'; // Default currency

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = trim($_POST['company_name'] ?? '');
    $logo_url = trim($_POST['logo_url'] ?? ''); // Basic handling for now
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currency = trim($_POST['currency'] ?? 'MYR');

    // Basic Validation
    if (empty($company_name)) {
        $errors[] = 'Company name is required.';
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    if (empty($currency)) {
        $errors[] = 'Currency is required.';
    }
     // Add more validation as needed for other fields (length, format, etc.)

    if (empty($errors)) {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("INSERT INTO companies (admin_id, name, logo_url, address, phone, email, currency) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$admin_id, $company_name, $logo_url, $address, $phone, $email, $currency])) {
                $_SESSION['message'] = 'Company added successfully!';
                header('Location: companies.php');
                exit;
            } else {
                $errors[] = 'Failed to add company. Please try again.';
            }
        } catch (PDOException $e) {
            error_log("Company Add Error: " . $e->getMessage());
            // Check for unique constraint violation if you have one on company name per admin
            if ($e->errorInfo[1] == 1062) { // Error code for duplicate entry
                $errors[] = 'A company with this name might already exist.';
            } else {
                $errors[] = 'An error occurred while adding the company. Please try again later.';
            }
        }
    }
}

include 'templates/partials/header.php';
?>

<div class="container">
    <h2>Add New Company</h2>

    <?php if (!empty($errors)): ?>
            <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="company_add.php" method="POST">
        <div>
            <label for="company_name">Company Name:</label>
            <input type="text" name="company_name" id="company_name" value="<?php echo htmlspecialchars($company_name); ?>" required>
        </div>
        <div>
            <label for="logo_url">Logo URL (optional):</label>
            <input type="text" name="logo_url" id="logo_url" value="<?php echo htmlspecialchars($logo_url); ?>">
        </div>
        <div>
            <label for="address">Address:</label>
            <textarea name="address" id="address"><?php echo htmlspecialchars($address); ?></textarea>
        </div>
        <div>
            <label for="phone">Phone:</label>
            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>">
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">
        </div>
        <div>
            <label for="currency">Currency:</label>
            <input type="text" name="currency" id="currency" value="<?php echo htmlspecialchars($currency); ?>" required placeholder="e.g., MYR, USD">
        </div>
        <button type="submit">Add Company</button>
        <a href="companies.php">Cancel</a>
    </form>
</div>

<?php include 'templates/partials/footer.php'; ?>
