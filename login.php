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
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        'SELECT u.id, u.password, u.firstName, u.lastName, u.roleId, r.name AS roleName
         FROM users u JOIN roles r ON u.roleId = r.id
         WHERE u.email = ?'
    );
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['first_name'] = $user['firstName'];
            $_SESSION['last_name']  = $user['lastName'];
            $_SESSION['role_id']    = $user['roleId'];
            $_SESSION['role_name']  = $user['roleName'];

            $routineCheck = $conn->prepare('SELECT id FROM userAvailability WHERE userId = ? LIMIT 1');
            $routineCheck->bind_param('i', $user['id']);
            $routineCheck->execute();
            $hasRoutine = $routineCheck->get_result()->num_rows > 0;
            $routineCheck->close();

            if (!$hasRoutine && in_array($user['roleName'], ['Skill Provider', 'Skill Seeker'], true)) {
                set_flash('Please set your weekly availability before continuing.', 'info');
                header('Location: ' . BASE_URL . '/routine.php');
            } else {
                header('Location: ' . BASE_URL . '/dashboard.php');
            }
            exit;
        }
    }
    $stmt->close();
    $errors[] = 'Invalid email or password.';
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width:420px;margin:0 auto;">
    <h2>Log in</h2>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= h($error) ?></div>
    <?php endforeach; ?>

    <form class="stacked" method="POST" action="<?= BASE_URL ?>/login.php">
        <label>Email
            <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button class="btn" type="submit">Log in</button>
    </form>
    <p style="margin-top:14px;">No account yet? <a href="<?= BASE_URL ?>/register.php">Register</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
