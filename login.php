<?php
session_start();
require_once 'config.php';
require_once 'src/includes/db.php';
require_once 'src/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                header('Location: admin_dashboard.php');
                exit;
            } else {
                $errors[] = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            $errors[] = 'An error occurred during login. Please try again later.';
        }
    }
}

if(isset($_SESSION['message'])) {
    $success_message = $_SESSION['message'];
    unset($_SESSION['message']);
}
if(isset($_GET['logged_out'])) {
    $success_message = 'You have been successfully logged out.';
}


include 'templates/partials/header.php';
?>

<div class="container">
    <h2>Admin Login</h2>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (isset($success_message)): ?>
        <div class="success"><p><?php echo htmlspecialchars($success_message); ?></p></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div>
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</div>

<?php include 'templates/partials/footer.php'; ?>
