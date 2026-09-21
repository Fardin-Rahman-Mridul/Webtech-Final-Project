<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('Admin');

$adminId = current_user_id();

// Change a user's role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_role') {
    $targetId = (int) $_POST['userId'];
    $newRoleId = (int) $_POST['roleId'];
    $stmt = $conn->prepare('UPDATE users SET roleId = ? WHERE id = ?');
    $stmt->bind_param('ii', $newRoleId, $targetId);
    $stmt->execute();
    set_flash('User role updated.', 'success');
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

// Delete a user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $targetId = (int) $_POST['userId'];
    if ($targetId === $adminId) {
        set_flash('You cannot delete your own account here.', 'error');
    } else {
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $targetId);
        $stmt->execute();
        set_flash('User deleted.', 'success');
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$users = $conn->query(
    'SELECT u.id, u.firstName, u.lastName, u.email, u.roleId, r.name AS roleName, u.createdAt
     FROM users u JOIN roles r ON u.roleId = r.id
     ORDER BY u.createdAt DESC'
);
$roles = $conn->query('SELECT id, name FROM roles ORDER BY id');
$rolesArr = [];
while ($row = $roles->fetch_assoc()) { $rolesArr[] = $row; }

$pageTitle = 'Manage Users';
require __DIR__ . '/../includes/header.php';
?>
<h1>Manage Users</h1>

<div class="card">
    <table>
        <tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr>
        <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?= h($u['firstName'] . ' ' . $u['lastName']) ?></td>
                <td><?= h($u['email']) ?></td>
                <td>
                    <form method="POST" action="<?= BASE_URL ?>/admin/users.php" style="display:flex;gap:6px;">
                        <input type="hidden" name="action" value="change_role">
                        <input type="hidden" name="userId" value="<?= (int) $u['id'] ?>">
                        <select name="roleId" onchange="this.form.submit()">
                            <?php foreach ($rolesArr as $role): ?>
                                <option value="<?= (int) $role['id'] ?>" <?= $role['id'] == $u['roleId'] ? 'selected' : '' ?>>
                                    <?= h($role['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td><?= h(date('M j, Y', strtotime($u['createdAt']))) ?></td>
                <td>
                    <form method="POST" action="<?= BASE_URL ?>/admin/users.php" onsubmit="return confirm('Delete this user permanently?');" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="userId" value="<?= (int) $u['id'] ?>">
                        <button class="btn btn-danger btn-sm" type="submit" <?= $u['id'] == $adminId ? 'disabled' : '' ?>>Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
