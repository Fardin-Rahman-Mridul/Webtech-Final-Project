<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('Skill Provider');

$userId = current_user_id();

// Delete a skill
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $skillId = (int) $_POST['skillId'];
    $stmt = $conn->prepare('DELETE FROM skills WHERE id = ? AND providerId = ?');
    $stmt->bind_param('ii', $skillId, $userId);
    $stmt->execute();
    $stmt->close();
    set_flash('Skill removed.', 'success');
    header('Location: ' . BASE_URL . '/provider/skills.php');
    exit;
}

$stmt = $conn->prepare(
    "SELECT s.id, s.name, s.proficiencyLevel, s.createdAt,
            COUNT(er.id) AS totalRequests,
            SUM(CASE WHEN er.status = 'Completed' THEN 1 ELSE 0 END) AS completedRequests
     FROM skills s
     LEFT JOIN exchangeRequests er ON er.skillId = s.id
     WHERE s.providerId = ?
     GROUP BY s.id
     ORDER BY s.createdAt DESC"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$skills = $stmt->get_result();

$pageTitle = 'My Skills';
require __DIR__ . '/../includes/header.php';
?>
<h1>My Skills</h1>
<a class="btn" href="<?= BASE_URL ?>/provider/add_skill.php" style="margin-bottom:16px;display:inline-block;">+ Add a Skill</a>

<?php if ($skills->num_rows === 0): ?>
    <div class="card"><p style="color:var(--muted);">You haven't listed any skills yet.</p></div>
<?php else: ?>
    <div class="card">
        <table>
            <tr><th>Skill</th><th>Level</th><th>Requests</th><th>Completed</th><th>Listed</th><th></th></tr>
            <?php while ($skill = $skills->fetch_assoc()): ?>
                <tr>
                    <td><?= h($skill['name']) ?></td>
                    <td><?= h($skill['proficiencyLevel']) ?></td>
                    <td><?= (int) $skill['totalRequests'] ?></td>
                    <td><?= (int) $skill['completedRequests'] ?></td>
                    <td><?= h(date('M j, Y', strtotime($skill['createdAt']))) ?></td>
                    <td>
                        <form method="POST" action="<?= BASE_URL ?>/provider/skills.php" onsubmit="return confirm('Remove this skill?');" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="skillId" value="<?= (int) $skill['id'] ?>">
                            <button class="btn btn-danger btn-sm" type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
