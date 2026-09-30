<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$siteTitle = 'Register';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'patient';
    $specialty = trim($_POST['specialty'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'Name, email, and password are required.';
    }

    if ($role === 'doctor' && $specialty === '') {
        $errors[] = 'Doctors must provide a specialty.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if (!$errors) {
        $pdo = getDb();
        $exists = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $exists->execute([':email' => strtolower($email)]);

        if ($exists->fetch()) {
            $errors[] = 'That email is already registered.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, specialty, created_at) VALUES (:name, :email, :password_hash, :role, :specialty, :created_at)');
            $stmt->execute([
                ':name' => $name,
                ':email' => strtolower($email),
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => $role,
                ':specialty' => $specialty,
                ':created_at' => date('Y-m-d H:i:s'),
            ]);

            redirect('login.php');
        }
    }
}

include __DIR__ . '/templates/header.php';
?>

<div class="page-shell">
    <div class="card auth-card">
        <h2>Create account</h2>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php">
            <div class="form-group">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="patient" <?php echo (($_POST['role'] ?? 'patient') === 'patient') ? 'selected' : ''; ?>>Patient</option>
                    <option value="doctor" <?php echo (($_POST['role'] ?? '') === 'doctor') ? 'selected' : ''; ?>>Doctor</option>
                </select>
            </div>

            <div class="form-group">
                <label for="specialty">Specialty</label>
                <input type="text" id="specialty" name="specialty" value="<?php echo htmlspecialchars($_POST['specialty'] ?? ''); ?>" placeholder="e.g. Cardiology">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>

        <p class="switch-link">
            Already registered? <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
