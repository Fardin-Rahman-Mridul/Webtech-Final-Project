<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('Skill Seeker');

$userId = current_user_id();

$sent = $conn->prepare('SELECT COUNT(*) AS c FROM exchangeRequests WHERE seekerId = ?');
$sent->bind_param('i', $userId);
$sent->execute();
$sent = $sent->get_result()->fetch_assoc()['c'];

$pending = $conn->prepare("SELECT COUNT(*) AS c FROM exchangeRequests WHERE seekerId = ? AND status = 'Pending'");
$pending->bind_param('i', $userId);
$pending->execute();
$pending = $pending->get_result()->fetch_assoc()['c'];

$completed = $conn->prepare("SELECT COUNT(*) AS c FROM exchangeRequests WHERE seekerId = ? AND status = 'Completed'");
$completed->bind_param('i', $userId);
$completed->execute();
$completed = $completed->get_result()->fetch_assoc()['c'];

$skillCount = $conn->query('SELECT COUNT(*) AS c FROM skills')->fetch_assoc()['c'];

$pageTitle = 'Seeker Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-banner">
    <img src="<?= h(profile_image_url($userId)) ?>" alt="Your profile picture">
    <div>
        <h1>Welcome back, <?= h($_SESSION['first_name']) ?> 👋</h1>
        <p>Find a skill to learn or check on your requests.</p>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card"><div class="num"><?= (int) $skillCount ?></div><div class="label">Skills Available</div></div>
    <div class="stat-card"><div class="num"><?= (int) $sent ?></div><div class="label">Requests Sent</div></div>
    <div class="stat-card"><div class="num"><?= (int) $pending ?></div><div class="label">Pending</div></div>
    <div class="stat-card"><div class="num"><?= (int) $completed ?></div><div class="label">Completed</div></div>
</div>

<div class="card">
    <h3>Quick Actions</h3>
    <a class="btn" href="<?= BASE_URL ?>/seeker/browse_skills.php">Browse Skills</a>
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/seeker/my_requests.php">My Requests</a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
