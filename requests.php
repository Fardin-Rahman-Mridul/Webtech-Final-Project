<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('Skill Provider');

$userId = current_user_id();

function get_day_name($dayIndex) {
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    return $days[$dayIndex] ?? 'Unknown';
}

function get_common_free_times($conn, $seekerId, $providerId) {
    $seekerStmt = $conn->prepare('SELECT dayOfWeek, startTime, endTime FROM userAvailability WHERE userId = ? ORDER BY dayOfWeek, startTime');
    $seekerStmt->bind_param('i', $seekerId);
    $seekerStmt->execute();
    $seekerRows = $seekerStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $seekerStmt->close();

    $providerStmt = $conn->prepare('SELECT dayOfWeek, startTime, endTime FROM userAvailability WHERE userId = ? ORDER BY dayOfWeek, startTime');
    $providerStmt->bind_param('i', $providerId);
    $providerStmt->execute();
    $providerRows = $providerStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $providerStmt->close();

    $matches = [];

    foreach ($seekerRows as $seeker) {
        foreach ($providerRows as $provider) {
            if ((int) $seeker['dayOfWeek'] !== (int) $provider['dayOfWeek']) {
                continue;
            }

            $start = max(strtotime($seeker['startTime']), strtotime($provider['startTime']));
            $end = min(strtotime($seeker['endTime']), strtotime($provider['endTime']));

            if ($start < $end) {
                $matches[] = [
                    'day' => (int) $seeker['dayOfWeek'],
                    'start' => date('H:i', $start),
                    'end' => date('H:i', $end),
                ];
            }
        }
    }

    usort($matches, function ($a, $b) {
        return [$a['day'], $a['start']] <=> [$b['day'], $b['start']];
    });

    return $matches;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = (int) ($_POST['requestId'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'accept') {
        $stmt = $conn->prepare("UPDATE exchangeRequests SET status = 'Accepted' WHERE id = ? AND providerId = ?");
        $stmt->bind_param('ii', $requestId, $userId);
        $stmt->execute();
        set_flash('Request accepted.', 'success');
        header('Location: ' . BASE_URL . '/provider/requests.php?highlight=' . $requestId);
        exit;
    } elseif ($action === 'decline') {
        $stmt = $conn->prepare("UPDATE exchangeRequests SET status = 'Declined' WHERE id = ? AND providerId = ?");
        $stmt->bind_param('ii', $requestId, $userId);
        $stmt->execute();
        set_flash('Request declined.', 'info');
    } elseif ($action === 'complete') {
        $stmt = $conn->prepare("UPDATE exchangeRequests SET status = 'Completed', completedAt = NOW() WHERE id = ? AND providerId = ?");
        $stmt->bind_param('ii', $requestId, $userId);
        $stmt->execute();
        set_flash('Exchange marked as completed.', 'success');
    }
    header('Location: ' . BASE_URL . '/provider/requests.php');
    exit;
}

$highlightRequestId = (int) ($_GET['highlight'] ?? 0);
$matchingSlots = [];
if ($highlightRequestId > 0) {
    $requestMatch = $conn->prepare('SELECT seekerId, providerId FROM exchangeRequests WHERE id = ? AND providerId = ?');
    $requestMatch->bind_param('ii', $highlightRequestId, $userId);
    $requestMatch->execute();
    $requestMatch = $requestMatch->get_result()->fetch_assoc();

    if ($requestMatch) {
        $matchingSlots = get_common_free_times($conn, $requestMatch['seekerId'], $requestMatch['providerId']);
    }
}

$stmt = $conn->prepare(
    "SELECT er.id, er.status, er.requestedAt, s.name AS skillName, u.firstName, u.lastName
     FROM exchangeRequests er
     JOIN skills s ON er.skillId = s.id
     JOIN users u ON er.seekerId = u.id
     WHERE er.providerId = ?
     ORDER BY FIELD(er.status,'Pending','Accepted','Completed','Declined'), er.requestedAt DESC"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$requests = $stmt->get_result();

$pageTitle = 'Exchange Requests';
require __DIR__ . '/../includes/header.php';
?>
<h1>Exchange Requests</h1>

<?php if (!empty($matchingSlots)): ?>
    <div class="card">
        <h3>Shared Free Time After Acceptance</h3>
        <ul>
            <?php foreach ($matchingSlots as $slot): ?>
                <li><strong><?= h(get_day_name($slot['day'])) ?></strong>: <?= h($slot['start']) ?> to <?= h($slot['end']) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php elseif ($highlightRequestId > 0): ?>
    <div class="card">
        <h3>Shared Free Time After Acceptance</h3>
        <p style="color:var(--muted);">No overlapping free time was found for this request yet. Please update both routines.</p>
    </div>
<?php endif; ?>

<?php if ($requests->num_rows === 0): ?>
    <div class="card"><p style="color:var(--muted);">No exchange requests yet.</p></div>
<?php else: ?>
    <div class="card">
        <table>
            <tr><th>Skill</th><th>Seeker</th><th>Requested</th><th>Status</th><th>Action</th></tr>
            <?php while ($r = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= h($r['skillName']) ?></td>
                    <td><?= h($r['firstName'] . ' ' . $r['lastName']) ?></td>
                    <td><?= h(date('M j, Y', strtotime($r['requestedAt']))) ?></td>
                    <td><span class="badge badge-<?= h($r['status']) ?>"><?= h($r['status']) ?></span></td>
                    <td>
                        <?php if ($r['status'] === 'Pending'): ?>
                            <form method="POST" action="<?= BASE_URL ?>/provider/requests.php" style="display:inline;">
                                <input type="hidden" name="requestId" value="<?= (int) $r['id'] ?>">
                                <input type="hidden" name="action" value="accept">
                                <button class="btn btn-sm" type="submit">Accept</button>
                            </form>
                            <form method="POST" action="<?= BASE_URL ?>/provider/requests.php" style="display:inline;">
                                <input type="hidden" name="requestId" value="<?= (int) $r['id'] ?>">
                                <input type="hidden" name="action" value="decline">
                                <button class="btn btn-danger btn-sm" type="submit">Decline</button>
                            </form>
                        <?php elseif ($r['status'] === 'Accepted'): ?>
                            <a class="btn btn-sm" href="<?= BASE_URL ?>/chat.php?requestId=<?= (int) $r['id'] ?>">Message</a>
                            <form method="POST" action="<?= BASE_URL ?>/provider/requests.php" style="display:inline;">
                                <input type="hidden" name="requestId" value="<?= (int) $r['id'] ?>">
                                <input type="hidden" name="action" value="complete">
                                <button class="btn btn-sm" type="submit">Mark Completed</button>
                            </form>
                        <?php else: ?>
                            <span style="color:var(--muted);">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
