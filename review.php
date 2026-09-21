<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('Skill Seeker');

$userId = current_user_id();
$requestId = (int) ($_GET['requestId'] ?? $_POST['requestId'] ?? 0);

// Load and validate the request belongs to this seeker and is completed
$stmt = $conn->prepare(
    "SELECT er.id, er.providerId, er.status, s.name AS skillName
     FROM exchangeRequests er JOIN skills s ON er.skillId = s.id
     WHERE er.id = ? AND er.seekerId = ?"
);
$stmt->bind_param('ii', $requestId, $userId);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request || $request['status'] !== 'Completed') {
    set_flash('That exchange is not available for review.', 'error');
    header('Location: ' . BASE_URL . '/seeker/my_requests.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int) ($_POST['rating'] ?? 0);
    $writtenReview = trim($_POST['writtenReview'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Please select a rating between 1 and 5.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            'INSERT INTO exchangeReviews (exchangeRequestId, reviewerId, revieweeId, rating, writtenReview)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iiiis', $requestId, $userId, $request['providerId'], $rating, $writtenReview);
        $stmt->execute();
        $stmt->close();
        set_flash('Thanks for your review!', 'success');
        header('Location: ' . BASE_URL . '/seeker/my_requests.php');
        exit;
    }
}

$pageTitle = 'Leave a Review';
require __DIR__ . '/../includes/header.php';
?>
<h1>Review: <?= h($request['skillName']) ?></h1>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:480px;">
    <form class="stacked" method="POST" action="<?= BASE_URL ?>/seeker/review.php">
        <input type="hidden" name="requestId" value="<?= (int) $requestId ?>">
        <label>Rating
            <div class="star-picker">
                <span class="star" data-value="1">★</span>
                <span class="star" data-value="2">★</span>
                <span class="star" data-value="3">★</span>
                <span class="star" data-value="4">★</span>
                <span class="star" data-value="5">★</span>
            </div>
            <span id="ratingLabel" style="font-weight:normal;color:var(--muted);font-size:0.9rem;">Click a star to rate</span>
            <input type="hidden" name="rating" id="ratingInput" value="">
        </label>
        <label>Your Review
            <textarea name="writtenReview" placeholder="Share your experience..."></textarea>
        </label>
        <button class="btn" type="submit">Submit Review</button>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
