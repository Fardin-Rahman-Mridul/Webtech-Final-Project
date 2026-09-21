<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName  = trim($_POST['lastName'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $roleId    = (int) ($_POST['roleId'] ?? 0);

    if ($firstName === '' || $lastName === '' || $email === '' || $password === '' || $roleId === 0) {
        $errors[] = 'Please fill in all fields, including your role.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'An account with that email already exists.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            'INSERT INTO users (email, password, firstName, lastName, roleId) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssssi', $email, $hashed, $firstName, $lastName, $roleId);
        if ($stmt->execute()) {
            set_flash('Account created! Please log in.', 'success');
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        } else {
            $errors[] = 'Something went wrong. Please try again.';
        }
        $stmt->close();
    }
}

$roles = $conn->query('SELECT id, name FROM roles WHERE name != "Admin" ORDER BY id');

$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width:480px;margin:0 auto;">
    <h2>Create your account</h2>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= h($error) ?></div>
    <?php endforeach; ?>

    <form class="stacked" method="POST" action="<?= BASE_URL ?>/register.php">
        <label>First Name
            <input type="text" name="firstName" value="<?= h($_POST['firstName'] ?? '') ?>" required>
        </label>
        <label>Last Name
            <input type="text" name="lastName" value="<?= h($_POST['lastName'] ?? '') ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <label>I am a...
            <select name="roleId" required>
                <option value="">Select a role</option>
                <?php while ($role = $roles->fetch_assoc()): ?>
                    <option value="<?= (int) $role['id'] ?>" <?= (($_POST['roleId'] ?? '') == $role['id']) ? 'selected' : '' ?>>
                        <?= h($role['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label>
        <button class="btn" type="submit">Register</button>
    </form>
    <p style="margin-top:14px;">Already have an account? <a href="<?= BASE_URL ?>/login.php">Log in</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
