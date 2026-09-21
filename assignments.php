<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('Skill Seeker');

$userId = current_user_id();
$errors = [];
$allowedSubmissionTypes = [
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    'application/vnd.ms-powerpoint' => 'ppt',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
    'text/plain' => 'txt',
    'application/zip' => 'zip',
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit') {
    $assignmentId = (int) ($_POST['assignmentId'] ?? 0);
    $assignmentStmt = $conn->prepare('SELECT id, deadline FROM assignments WHERE id = ? AND seekerId = ?');
    $assignmentStmt->bind_param('ii', $assignmentId, $userId);
    $assignmentStmt->execute();
    $assignment = $assignmentStmt->get_result()->fetch_assoc();
    $assignmentStmt->close();

    if (!$assignment) {
        $errors[] = 'That assignment could not be found.';
    } else {
        $submission = store_uploaded_file($_FILES['submission'] ?? null, 'submissions', $allowedSubmissionTypes);
        if (isset($submission['error'])) {
            $errors[] = $submission['error'];
        }
    }

    if (empty($errors)) {
        $status = strtotime($assignment['deadline']) < time() ? 'Late' : 'On Time';
        $stmt = $conn->prepare(
            'INSERT INTO assignmentSubmissions (assignmentId, seekerId, fileName, filePath, status)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iisss', $assignmentId, $userId, $submission['name'], $submission['path'], $status);
        $stmt->execute();
        $stmt->close();
        set_flash('Assignment submitted.', 'success');
        header('Location: ' . BASE_URL . '/seeker/assignments.php');
        exit;
    }
}

$stmt = $conn->prepare(
    "SELECT a.id, a.title, a.description, a.deadline, a.createdAt,
            a.fileName AS assignmentFileName, a.filePath AS assignmentFilePath,
            u.firstName, u.lastName,
            sub.fileName AS submissionFileName, sub.filePath AS submissionFilePath,
            sub.submittedAt, sub.status AS submissionStatus
     FROM assignments a
     JOIN users u ON u.id = a.providerId
     LEFT JOIN (
         SELECT assignmentId, MAX(id) AS latestId
         FROM assignmentSubmissions
         WHERE seekerId = ?
         GROUP BY assignmentId
     ) latest ON latest.assignmentId = a.id
     LEFT JOIN assignmentSubmissions sub ON sub.id = latest.latestId
     WHERE a.seekerId = ?
     ORDER BY a.deadline ASC, a.createdAt DESC"
);
$stmt->bind_param('ii', $userId, $userId);
$stmt->execute();
$assignments = $stmt->get_result();

$pageTitle = 'Assignments';
require __DIR__ . '/../includes/header.php';
?>
<h1>Assignments</h1>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endforeach; ?>

<?php if ($assignments->num_rows === 0): ?>
    <div class="card"><p style="color:var(--muted);">No assignments have been sent to you yet.</p></div>
<?php else: ?>
    <?php while ($assignment = $assignments->fetch_assoc()): ?>
        <div class="card">
            <h3><?= h($assignment['title']) ?></h3>
            <p style="color:var(--muted);">From <?= h($assignment['firstName'] . ' ' . $assignment['lastName']) ?> · Due <?= h(date('M j, Y g:i A', strtotime($assignment['deadline']))) ?></p>
            <?php if ($assignment['description']): ?>
                <p><?= nl2br(h($assignment['description'])) ?></p>
            <?php endif; ?>
            <?php if ($assignment['assignmentFilePath']): ?>
                <p><a href="<?= h(BASE_URL . '/' . $assignment['assignmentFilePath']) ?>" target="_blank" rel="noopener">Download assignment file</a></p>
            <?php endif; ?>

            <?php if ($assignment['submissionFilePath']): ?>
                <p class="alert alert-success">
                    Submitted <?= h(date('M j, Y g:i A', strtotime($assignment['submittedAt']))) ?>
                    (<?= h($assignment['submissionStatus']) ?>):
                    <a href="<?= h(BASE_URL . '/' . $assignment['submissionFilePath']) ?>" target="_blank" rel="noopener"><?= h($assignment['submissionFileName']) ?></a>
                </p>
                <h3>Submit a new version</h3>
            <?php else: ?>
                <h3>Submit your work</h3>
            <?php endif; ?>
            <form class="stacked" method="POST" action="<?= BASE_URL ?>/seeker/assignments.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="submit">
                <input type="hidden" name="assignmentId" value="<?= (int) $assignment['id'] ?>">
                <label>File
                    <input type="file" name="submission" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.jpg,.jpeg,.png" required>
                </label>
                <p style="color:var(--muted);font-size:0.85rem;">Files must be 10 MB or smaller. Late submissions are marked automatically.</p>
                <button class="btn" type="submit">Submit File</button>
            </form>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
