<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}
$pageTitle = 'Welcome';
require __DIR__ . '/includes/header.php';
?>
<section class="hero-split">
    <div>
        <h1>Trade skills like<br>flashcards.</h1>
        <p class="lede">StudySwap connects students who want to teach with students who want to learn — sign in, list what you know, and swap it for something new.</p>
        <div class="hero-actions">
            <a class="btn" href="<?= BASE_URL ?>/register.php">Get Started</a>
            <a class="btn btn-secondary" href="<?= BASE_URL ?>/login.php">Login</a>
        </div>
    </div>
    <!--
    <div class="card-deck">
        <img src="https://pin.it/3Wz5Vs7nF" alt="A student coding on a laptop">
        <img src="https://picsum.photos/seed/studyswap-group/400/500" alt="Students collaborating around a table">
        <img src="https://picsum.photos/seed/studyswap-design/400/500" alt="A student sketching a design">
    </div>
    -->
</section>

<div class="role-grid">
    <div class="role-card provider">
        <h3>🎓 Skill Providers</h3>
        <p>List the skills you can teach, review incoming requests, and track who you've helped.</p>
    </div>
    <div class="role-card seeker">
        <h3>🔍 Skill Seekers</h3>
        <p>Browse and search skills offered by other students, send exchange requests, and rate providers.</p>
    </div>
    <div class="role-card admin">
        <h3>🛠️ Admins</h3>
        <p>Manage users and roles, monitor every exchange on the platform, and generate activity reports.</p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
