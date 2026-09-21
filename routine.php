<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

if (!in_array(current_role(), ['Skill Provider', 'Skill Seeker'], true)) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$userId = current_user_id();
$days = [
    'Sunday' => 0,
    'Monday' => 1,
    'Tuesday' => 2,
    'Wednesday' => 3,
    'Thursday' => 4,
    'Friday' => 5,
    'Saturday' => 6,
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_routine') {
    $conn->begin_transaction();

    $deleteStmt = $conn->prepare('DELETE FROM userAvailability WHERE userId = ?');
    $deleteStmt->bind_param('i', $userId);
    $deleteStmt->execute();
    $deleteStmt->close();

    $insertStmt = $conn->prepare('INSERT INTO userAvailability (userId, dayOfWeek, startTime, endTime) VALUES (?, ?, ?, ?)');

    foreach ($days as $label => $dayIndex) {
        $start = trim($_POST['start_' . $label] ?? '');
        $end = trim($_POST['end_' . $label] ?? '');

        if ($start === '' && $end === '') {
            continue;
        }

        if ($start === '' || $end === '') {
            $errors[] = 'Please provide both a start and end time for ' . $label . '.';
            continue;
        }

        if ($start >= $end) {
            $errors[] = 'The end time must be later than the start time for ' . $label . '.';
            continue;
        }

        $insertStmt->bind_param('iiss', $userId, $dayIndex, $start, $end);
        $insertStmt->execute();
    }

    $insertStmt->close();

    if (empty($errors)) {
        $conn->commit();
        set_flash('Your weekly availability has been saved.', 'success');
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }

    $conn->rollback();
}

$availabilityStmt = $conn->prepare('SELECT dayOfWeek, startTime, endTime FROM userAvailability WHERE userId = ? ORDER BY dayOfWeek');
$availabilityStmt->bind_param('i', $userId);
$availabilityStmt->execute();
$availabilityRows = $availabilityStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$availability = [];
foreach ($availabilityRows as $row) {
    $availability[(int) $row['dayOfWeek']] = [
        'start' => $row['startTime'],
        'end' => $row['endTime'],
    ];
}
$availabilityStmt->close();

$pageTitle = 'My Availability';
require __DIR__ . '/includes/header.php';
?>
<h1>My Weekly Availability</h1>
<p style="color:var(--muted);">Set the hours you are free from Sunday to Saturday. Leave a day blank if you are unavailable.</p>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endforeach; ?>

<div class="card">
    <form method="POST" action="<?= BASE_URL ?>/routine.php" class="stacked">
        <input type="hidden" name="action" value="save_routine">

        <table>
            <tr>
                <th>Day</th>
                <th>Available From</th>
                <th>Available Until</th>
            </tr>
            <?php foreach ($days as $label => $dayIndex): ?>
                <tr>
                    <td><strong><?= h($label) ?></strong></td>
                    <td>
                        <input type="time" name="start_<?= h($label) ?>" value="<?= h($availability[$dayIndex]['start'] ?? '') ?>">
                    </td>
                    <td>
                        <input type="time" name="end_<?= h($label) ?>" value="<?= h($availability[$dayIndex]['end'] ?? '') ?>">
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <button class="btn" type="submit">Save Weekly Schedule</button>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
