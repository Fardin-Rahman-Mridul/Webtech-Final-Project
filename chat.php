<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$userId = current_user_id();
$errors = [];

$requestId = (int) ($_GET['requestId'] ?? 0);
if ($requestId <= 0) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$stmt = $conn->prepare(
    'SELECT er.id, er.status, er.seekerId, er.providerId, s.name AS skillName,
            seeker.firstName AS seekerFirst, seeker.lastName AS seekerLast,
            provider.firstName AS providerFirst, provider.lastName AS providerLast
     FROM exchangeRequests er
     JOIN skills s ON er.skillId = s.id
     JOIN users seeker ON er.seekerId = seeker.id
     JOIN users provider ON er.providerId = provider.id
     WHERE er.id = ? AND (er.seekerId = ? OR er.providerId = ?)'
);
$stmt->bind_param('iii', $requestId, $userId, $userId);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$request || $request['status'] !== 'Accepted') {
    set_flash('You can only chat after the request is accepted.', 'error');
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$otherUserId = ($request['seekerId'] == $userId) ? (int) $request['providerId'] : (int) $request['seekerId'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'send_message') {
    $message = trim($_POST['message'] ?? '');
    if ($message === '') {
        $errors[] = 'Message cannot be empty.';
    } else {
        $insert = $conn->prepare('INSERT INTO exchangeMessages (requestId, senderId, receiverId, message) VALUES (?, ?, ?, ?)');
        $insert->bind_param('iiis', $requestId, $userId, $otherUserId, $message);
        $insert->execute();
        $insert->close();
        header('Location: ' . BASE_URL . '/chat.php?requestId=' . $requestId);
        exit;
    }
}

$messagesStmt = $conn->prepare(
    'SELECT em.id, em.message, em.sentAt, u.firstName, u.lastName, em.senderId
     FROM exchangeMessages em
     JOIN users u ON em.senderId = u.id
     WHERE em.requestId = ?
     ORDER BY em.sentAt ASC'
);
$messagesStmt->bind_param('i', $requestId);
$messagesStmt->execute();
$messages = $messagesStmt->get_result();
$messagesStmt->close();

$pageTitle = 'Chat';
require __DIR__ . '/includes/header.php';
?>
<h1>Chat for <?= h($request['skillName']) ?></h1>
<p style="color:var(--muted); margin-bottom:1rem;">
    Chat with <?= h(($request['seekerId'] == $userId) ? ($request['providerFirst'] . ' ' . $request['providerLast']) : ($request['seekerFirst'] . ' ' . $request['seekerLast'])) ?>
</p>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:900px; margin:0 auto;">
    <div style="max-height:420px; overflow-y:auto; padding-right:8px;">
        <?php if ($messages->num_rows === 0): ?>
            <p style="color:var(--muted);">No messages yet. Start the conversation.</p>
        <?php else: ?>
            <?php while ($message = $messages->fetch_assoc()): ?>
                <div style="margin-bottom:12px; padding:10px 12px; border-radius:8px; background:<?= ($message['senderId'] == $userId) ? 'var(--primary-soft)' : '#f3f4f6' ?>; margin-left:<?= ($message['senderId'] == $userId) ? 'auto' : '0' ?>; max-width:75%;">
                    <div style="font-size:0.8rem; color:var(--muted); margin-bottom:5px;">
                        <?= h($message['firstName'] . ' ' . $message['lastName']) ?> · <?= h(date('M j, Y g:i a', strtotime($message['sentAt']))) ?>
                    </div>
                    <div><?= h($message['message']) ?></div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/chat.php?requestId=<?= (int) $requestId ?>" style="margin-top:20px;">
        <input type="hidden" name="action" value="send_message">
        <label>
            Message
            <textarea name="message" rows="3" placeholder="Type your message here..." required></textarea>
        </label>
        <button class="btn" type="submit">Send Message</button>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
