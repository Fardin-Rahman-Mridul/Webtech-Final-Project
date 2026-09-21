<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('Admin');

$statusFilter = $_GET['status'] ?? '';

$sql = "SELECT er.id, er.status, er.requestedAt, er.completedAt, s.name AS skillName,
               p.firstName AS pFirst, p.lastName AS pLast,
               sk.firstName AS sFirst, sk.lastName AS sLast
        FROM exchangeRequests er
        JOIN skills s ON er.skillId = s.id
        JOIN users p ON er.providerId = p.id
        JOIN users sk ON er.seekerId = sk.id";
$params = [];
$types = '';
if (in_array($statusFilter, ['Pending','Accepted','Declined','Completed'], true)) {
    $sql .= ' WHERE er.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}
$sql .= ' ORDER BY er.requestedAt DESC';

$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$exchanges = $stmt->get_result();

$pageTitle = 'Monitor Exchanges';
require __DIR__ . '/../includes/header.php';
?>
<h1>All Exchanges</h1>

<div style="margin-bottom:16px;">
    <a href="<?= BASE_URL ?>/admin/exchanges.php" class="btn btn-sm <?= $statusFilter === '' ? '' : 'btn-secondary' ?>">All</a>
    <a href="<?= BASE_URL ?>/admin/exchanges.php?status=Pending" class="btn btn-sm <?= $statusFilter === 'Pending' ? '' : 'btn-secondary' ?>">Pending</a>
    <a href="<?= BASE_URL ?>/admin/exchanges.php?status=Accepted" class="btn btn-sm <?= $statusFilter === 'Accepted' ? '' : 'btn-secondary' ?>">Accepted</a>
    <a href="<?= BASE_URL ?>/admin/exchanges.php?status=Completed" class="btn btn-sm <?= $statusFilter === 'Completed' ? '' : 'btn-secondary' ?>">Completed</a>
    <a href="<?= BASE_URL ?>/admin/exchanges.php?status=Declined" class="btn btn-sm <?= $statusFilter === 'Declined' ? '' : 'btn-secondary' ?>">Declined</a>
</div>

<div class="card">
    <?php if ($exchanges->num_rows === 0): ?>
        <p style="color:var(--muted);">No exchanges found.</p>
    <?php else: ?>
        <table>
            <tr><th>Skill</th><th>Provider</th><th>Seeker</th><th>Requested</th><th>Status</th></tr>
            <?php while ($e = $exchanges->fetch_assoc()): ?>
                <tr>
                    <td><?= h($e['skillName']) ?></td>
                    <td><?= h($e['pFirst'] . ' ' . $e['pLast']) ?></td>
                    <td><?= h($e['sFirst'] . ' ' . $e['sLast']) ?></td>
                    <td><?= h(date('M j, Y', strtotime($e['requestedAt']))) ?></td>
                    <td><span class="badge badge-<?= h($e['status']) ?>"><?= h($e['status']) ?></span></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
