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
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    // Basic validation for username characters (optional, but good practice)
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) { // Example: minimum password length
        $errors[] = 'Password must be at least 6 characters long.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        try {
            $pdo = get_db_connection();

            // Check if username exists
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = 'Username already taken.';
            }

            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered.';
            }

            if (empty($errors)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $email, $password_hash])) {
                    $_SESSION['message'] = 'Registration successful! Please login.';
                    header('Location: login.php');
                    exit;
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                }
            }
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            $errors[] = 'An error occurred during registration. Please try again later.';
        }
    }
}

include 'templates/partials/header.php';
?>

<div class="container">
    <h2>Admin Registration</h2>

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


    <form action="register.php" method="POST">
        <div>
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</div>

<?php include 'templates/partials/footer.php'; ?>
