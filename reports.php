<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('Admin');

// Users per role
$usersByRole = $conn->query(
    'SELECT r.name, COUNT(u.id) AS total FROM roles r
     LEFT JOIN users u ON u.roleId = r.id
     GROUP BY r.id'
);

// Requests by status
$requestsByStatus = $conn->query(
    "SELECT status, COUNT(*) AS total FROM exchangeRequests GROUP BY status"
);

// Top rated providers
$topProviders = $conn->query(
    "SELECT u.firstName, u.lastName, AVG(rv.rating) AS avgRating, COUNT(rv.id) AS reviewCount
     FROM exchangeReviews rv JOIN users u ON rv.revieweeId = u.id
     GROUP BY u.id
     ORDER BY avgRating DESC LIMIT 5"
);

// Most requested skills
$topSkills = $conn->query(
    "SELECT s.name, COUNT(er.id) AS requestCount
     FROM skills s LEFT JOIN exchangeRequests er ON er.skillId = s.id
     GROUP BY s.id
     ORDER BY requestCount DESC LIMIT 5"
);

$pageTitle = 'Reports';
require __DIR__ . '/../includes/header.php';
?>
<h1>Platform Reports</h1>

<div class="card-grid">
    <div class="card">
        <h3>Users by Role</h3>
        <table>
            <tr><th>Role</th><th>Count</th></tr>
            <?php while ($row = $usersByRole->fetch_assoc()): ?>
                <tr><td><?= h($row['name']) ?></td><td><?= (int) $row['total'] ?></td></tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="card">
        <h3>Requests by Status</h3>
        <table>
            <tr><th>Status</th><th>Count</th></tr>
            <?php while ($row = $requestsByStatus->fetch_assoc()): ?>
                <tr><td><span class="badge badge-<?= h($row['status']) ?>"><?= h($row['status']) ?></span></td><td><?= (int) $row['total'] ?></td></tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="card">
        <h3>Top Rated Providers</h3>
        <?php if ($topProviders->num_rows === 0): ?>
            <p style="color:var(--muted);">No reviews yet.</p>
        <?php else: ?>
            <table>
                <tr><th>Provider</th><th>Rating</th><th>Reviews</th></tr>
                <?php while ($row = $topProviders->fetch_assoc()): ?>
                    <tr>
                        <td><?= h($row['firstName'] . ' ' . $row['lastName']) ?></td>
                        <td><?= number_format($row['avgRating'], 1) ?> ★</td>
                        <td><?= (int) $row['reviewCount'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Most Requested Skills</h3>
        <table>
            <tr><th>Skill</th><th>Requests</th></tr>
            <?php while ($row = $topSkills->fetch_assoc()): ?>
                <tr><td><?= h($row['name']) ?></td><td><?= (int) $row['requestCount'] ?></td></tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
