<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('Skill Seeker');

$userId = current_user_id();

// Send a request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request') {
    $skillId = (int) $_POST['skillId'];
    $providerId = (int) $_POST['providerId'];

    // prevent duplicate pending requests for the same skill
    $check = $conn->prepare("SELECT id FROM exchangeRequests WHERE skillId = ? AND seekerId = ? AND status = 'Pending'");
    $check->bind_param('ii', $skillId, $userId);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        set_flash('You already have a pending request for this skill.', 'error');
    } else {
        $stmt = $conn->prepare('INSERT INTO exchangeRequests (skillId, seekerId, providerId) VALUES (?, ?, ?)');
        $stmt->bind_param('iii', $skillId, $userId, $providerId);
        $stmt->execute();
        $stmt->close();
        set_flash('Exchange request sent!', 'success');
    }
    header('Location: ' . BASE_URL . '/seeker/browse_skills.php');
    exit;
}

// Search / filter
$search = trim($_GET['q'] ?? '');
$sql = "SELECT s.id, s.name, s.proficiencyLevel, u.id AS providerId, u.firstName, u.lastName,
               (SELECT AVG(rating) FROM exchangeReviews WHERE revieweeId = u.id) AS avgRating
        FROM skills s
        JOIN users u ON s.providerId = u.id
        WHERE u.id != ?";
$params = [$userId];
$types = 'i';

if ($search !== '') {
    $sql .= ' AND s.name LIKE ?';
    $params[] = '%' . $search . '%';
    $types .= 's';
}
$sql .= ' ORDER BY s.createdAt DESC';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$skills = $stmt->get_result();

$pageTitle = 'Browse Skills';
require __DIR__ . '/../includes/header.php';
?>
<h1>Browse Skills</h1>

<form method="GET" action="<?= BASE_URL ?>/seeker/browse_skills.php" class="search-bar">
    <input type="text" name="q" placeholder="Search skills (e.g. Python, Design)..." value="<?= h($search) ?>">
    <button class="btn" type="submit">Search</button>
    <?php if ($search !== ''): ?><a href="<?= BASE_URL ?>/seeker/browse_skills.php" style="align-self:center;">Clear</a><?php endif; ?>
</form>

<?php if ($skills->num_rows === 0): ?>
    <div class="card"><p style="color:var(--muted);">No skills found.</p></div>
<?php else: ?>
    <div class="card-grid">
        <?php while ($skill = $skills->fetch_assoc()): ?>
            <div class="skill-card">
                <span class="level-tag"><?= h($skill['proficiencyLevel']) ?></span>
                <h3><?= h($skill['name']) ?></h3>
                <p style="margin:0 0 6px;">Provider: <?= h($skill['firstName'] . ' ' . $skill['lastName']) ?></p>
                <p style="margin:0 0 12px;" class="rating-stars">
                    <?= $skill['avgRating'] ? str_repeat('★', round($skill['avgRating'])) . ' (' . number_format($skill['avgRating'], 1) . ')' : 'No ratings yet' ?>
                </p>
                <form method="POST" action="<?= BASE_URL ?>/seeker/browse_skills.php">
                    <input type="hidden" name="action" value="request">
                    <input type="hidden" name="skillId" value="<?= (int) $skill['id'] ?>">
                    <input type="hidden" name="providerId" value="<?= (int) $skill['providerId'] ?>">
                    <button class="btn btn-sm" type="submit">Request Exchange</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
