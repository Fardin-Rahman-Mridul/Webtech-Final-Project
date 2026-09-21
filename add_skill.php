<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('Skill Provider');

$userId = current_user_id();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $level = trim($_POST['proficiencyLevel'] ?? '');

    if ($name === '' || $level === '') {
        $errors[] = 'Please fill in both fields.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('INSERT INTO skills (providerId, name, proficiencyLevel) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $userId, $name, $level);
        $stmt->execute();
        $stmt->close();
        set_flash('Skill added!', 'success');
        header('Location: ' . BASE_URL . '/provider/skills.php');
        exit;
    }
}

$pageTitle = 'Add a Skill';
require __DIR__ . '/../includes/header.php';
?>
<h1>Add a New Skill</h1>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:480px;">
    <form class="stacked" method="POST" action="<?= BASE_URL ?>/provider/add_skill.php">
        <label>Skill Name
            <input type="text" name="name" placeholder="e.g. Python Programming" value="<?= h($_POST['name'] ?? '') ?>" required>
        </label>
        <label>Proficiency Level
            <select name="proficiencyLevel" required>
                <option value="">Select level</option>
                <option>Beginner</option>
                <option>Intermediate</option>
                <option>Advanced</option>
            </select>
        </label>
        <button class="btn" type="submit">Add Skill</button>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
