<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$userId = current_user_id();
$errors = [];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName  = trim($_POST['lastName'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid name and email.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('UPDATE users SET firstName = ?, lastName = ?, email = ? WHERE id = ?');
        $stmt->bind_param('sssi', $firstName, $lastName, $email, $userId);
        $stmt->execute();
        $stmt->close();
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name']  = $lastName;
        set_flash('Profile updated.', 'success');
        header('Location: ' . BASE_URL . '/profile.php');
        exit;
    }
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'picture') {
    $upload = $_FILES['profilePicture'] ?? null;
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    if (!$upload || $upload['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Please choose an image to upload.';
    } elseif ($upload['size'] > 5 * 1024 * 1024) {
        $errors[] = 'Profile pictures must be 5 MB or smaller.';
    } else {
        $imageInfo = @getimagesize($upload['tmp_name']);
        $mimeType = $imageInfo['mime'] ?? '';

        if (!$imageInfo || !isset($allowedTypes[$mimeType])) {
            $errors[] = 'Please upload a valid JPG, PNG, GIF, or WebP image.';
        } else {
            $uploadDirectory = __DIR__ . '/uploads/profile-pictures';
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }

            foreach (glob($uploadDirectory . '/user_' . $userId . '.*') as $oldPicture) {
                unlink($oldPicture);
            }

            $picturePath = $uploadDirectory . '/user_' . $userId . '.' . $allowedTypes[$mimeType];
            if (move_uploaded_file($upload['tmp_name'], $picturePath)) {
                set_flash('Profile picture updated.', 'success');
                header('Location: ' . BASE_URL . '/profile.php');
                exit;
            }

            $errors[] = 'The profile picture could not be saved. Please try again.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'password') {
    $newPassword = $_POST['newPassword'] ?? '';
    if (strlen($newPassword) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bind_param('si', $hashed, $userId);
        $stmt->execute();
        $stmt->close();
        set_flash('Password changed successfully.', 'success');
        header('Location: ' . BASE_URL . '/profile.php');
        exit;
    }
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$stmt = $conn->prepare('SELECT email, firstName, lastName, createdAt FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$pageTitle = 'My Profile';
require __DIR__ . '/includes/header.php';
?>
<h1>My Profile</h1>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:520px;">
    <h3>Profile Information</h3>
    <form class="stacked" method="POST" action="<?= BASE_URL ?>/profile.php">
        <input type="hidden" name="action" value="update">
        <label>First Name
            <input type="text" name="firstName" value="<?= h($user['firstName']) ?>" required>
        </label>
        <label>Last Name
            <input type="text" name="lastName" value="<?= h($user['lastName']) ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= h($user['email']) ?>" required>
        </label>
        <p style="color:var(--muted);font-size:0.85rem;">Member since <?= h(date('M j, Y', strtotime($user['createdAt']))) ?> — Role: <?= h(current_role()) ?></p>
        <button class="btn" type="submit">Save Changes</button>
    </form>
</div>

<div class="card" style="max-width:520px;">
    <h3>Profile Picture</h3>
    <img class="profile-picture-preview" src="<?= h(profile_image_url($userId)) ?>" alt="Your profile picture">
    <form class="stacked" method="POST" action="<?= BASE_URL ?>/profile.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="picture">
        <label>Choose a picture
            <input type="file" name="profilePicture" accept="image/jpeg,image/png,image/gif,image/webp" required>
        </label>
        <p style="color:var(--muted);font-size:0.85rem;">JPG, PNG, GIF, or WebP up to 5 MB.</p>
        <button class="btn" type="submit">Upload Picture</button>
    </form>
</div>

<div class="card" style="max-width:520px;">
    <h3>Change Password</h3>
    <form class="stacked" method="POST" action="<?= BASE_URL ?>/profile.php">
        <input type="hidden" name="action" value="password">
        <label>New Password
            <input type="password" name="newPassword" required minlength="6">
        </label>
        <button class="btn" type="submit">Update Password</button>
    </form>
</div>

<div class="card" style="max-width:520px;">
    <h3>Delete Account</h3>
    <p style="color:var(--muted);">This will permanently remove your account and cannot be undone.</p>
    <form method="POST" action="<?= BASE_URL ?>/profile.php" onsubmit="return confirm('Are you sure you want to delete your account?');">
        <input type="hidden" name="action" value="delete">
        <button class="btn btn-danger" type="submit">Delete My Account</button>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
