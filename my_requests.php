<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('Skill Seeker');

$userId = current_user_id();

$stmt = $conn->prepare(
    "SELECT er.id, er.status, er.requestedAt, er.completedAt, s.name AS skillName,
            u.firstName, u.lastName,
            (SELECT COUNT(*) FROM exchangeReviews WHERE exchangeRequestId = er.id) AS reviewed
     FROM exchangeRequests er
     JOIN skills s ON er.skillId = s.id
     JOIN users u ON er.providerId = u.id
     WHERE er.seekerId = ?
     ORDER BY FIELD(er.status,'Pending','Accepted','Completed','Declined'), er.requestedAt DESC"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$requests = $stmt->get_result();

$pageTitle = 'My Requests';
require __DIR__ . '/../includes/header.php';
?>
<h1>My Requests</h1>

<?php if ($requests->num_rows === 0): ?>
    <div class="card"><p style="color:var(--muted);">You haven't sent any requests yet. <a href="<?= BASE_URL ?>/seeker/browse_skills.php">Browse skills</a> to get started.</p></div>
<?php else: ?>
    <div class="card">
        <table>
            <tr><th>Skill</th><th>Provider</th><th>Requested</th><th>Status</th><th></th></tr>
            <?php while ($r = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= h($r['skillName']) ?></td>
                    <td><?= h($r['firstName'] . ' ' . $r['lastName']) ?></td>
                    <td><?= h(date('M j, Y', strtotime($r['requestedAt']))) ?></td>
                    <td><span class="badge badge-<?= h($r['status']) ?>"><?= h($r['status']) ?></span></td>
                    <td>
                        <?php if ($r['status'] === 'Accepted'): ?>
                            <a class="btn btn-sm" href="<?= BASE_URL ?>/chat.php?requestId=<?= (int) $r['id'] ?>">Message</a>
                        <?php elseif ($r['status'] === 'Completed' && !$r['reviewed']): ?>
                            <a class="btn btn-sm" href="<?= BASE_URL ?>/seeker/review.php?requestId=<?= (int) $r['id'] ?>">Leave Review</a>
                        <?php elseif ($r['reviewed']): ?>
                            <span style="color:var(--muted);">Reviewed ✓</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
