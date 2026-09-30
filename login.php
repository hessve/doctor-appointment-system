<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

redirectIfLoggedOut();

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$siteTitle = 'Login';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    } else {
        $pdo = getDb();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => strtolower($email)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = (int) $user['id'];
            redirect('dashboard.php');
        }

        $errors[] = 'Invalid email or password.';
    }
}

include __DIR__ . '/templates/header.php';
?>

<div class="page-shell">
    <div class="card auth-card">
        <h2>Login</h2>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <p class="switch-link">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
