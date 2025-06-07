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
$company_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$errors = [];
$company = null;

if (!$company_id) {
    $_SESSION['message'] = 'Invalid company ID.';
    header('Location: companies.php');
    exit;
}

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ? AND admin_id = ?");
    $stmt->execute([$company_id, $admin_id]);
    $company = $stmt->fetch();

    if (!$company) {
        $_SESSION['message'] = 'Company not found or you do not have permission to edit it.';
        header('Location: companies.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Company Edit (Fetch) Error: " . $e->getMessage());
    $_SESSION['message'] = 'Error fetching company data.';
    header('Location: companies.php');
    exit;
}

// Initialize form variables with existing company data
$company_name = $company['name'];
$logo_url = $company['logo_url'];
$address = $company['address'];
$phone = $company['phone'];
$email = $company['email'];
$currency = $company['currency'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = trim($_POST['company_name'] ?? '');
    $logo_url = trim($_POST['logo_url'] ?? '');
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

    if (empty($errors)) {
        try {
            $pdo_update = get_db_connection(); // Use a new variable for clarity if needed, or reuse $pdo
            $update_stmt = $pdo_update->prepare("UPDATE companies SET name = ?, logo_url = ?, address = ?, phone = ?, email = ?, currency = ? WHERE id = ? AND admin_id = ?");
            if ($update_stmt->execute([$company_name, $logo_url, $address, $phone, $email, $currency, $company_id, $admin_id])) {
                $_SESSION['message'] = 'Company updated successfully!';
                header('Location: companies.php');
                exit;
            } else {
                $errors[] = 'Failed to update company. Please try again.';
            }
        } catch (PDOException $e) {
            error_log("Company Update Error: " . $e->getMessage());
             if ($e->errorInfo[1] == 1062) {
                $errors[] = 'A company with this name might already exist for your account.';
            } else {
                $errors[] = 'An error occurred while updating the company. Please try again later.';
            }
        }
    }
}

include 'templates/partials/header.php';
?>

<div class="container">
    <h2>Edit Company: <?php echo htmlspecialchars($company['name']); ?></h2>

    <?php if (!empty($errors)): ?>
            <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="company_edit.php?id=<?php echo $company_id; ?>" method="POST">
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
        <button type="submit">Update Company</button>
        <a href="companies.php">Cancel</a>
    </form>
</div>

<?php include 'templates/partials/footer.php'; ?>
