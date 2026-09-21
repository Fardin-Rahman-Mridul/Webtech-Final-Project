<?php
// Expects require_once for auth.php and functions.php to already have run.
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' – ' : '' ?>Study Swap</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= BASE_URL ?>/index.php"><span class="mark"></span>Study<span class="accent">Swap</span></a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">☰</button>
        <nav class="main-nav" id="mainNav">
            <?php if (is_logged_in()): ?>
                <a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a>
                <a href="<?= BASE_URL ?>/profile.php">Profile</a>
                <?php if (in_array(current_role(), ['Skill Provider', 'Skill Seeker'], true)): ?>
                    <a href="<?= BASE_URL ?>/routine.php">My Availability</a>
                <?php endif; ?>
                <?php if (current_role() === 'Skill Provider'): ?>
                    <a href="<?= BASE_URL ?>/provider/skills.php">My Skills</a>
                    <a href="<?= BASE_URL ?>/provider/requests.php">Requests</a>
                    <a href="<?= BASE_URL ?>/provider/assignments.php">Assignments</a>
                <?php elseif (current_role() === 'Skill Seeker'): ?>
                    <a href="<?= BASE_URL ?>/seeker/browse_skills.php">Browse Skills</a>
                    <a href="<?= BASE_URL ?>/seeker/my_requests.php">My Requests</a>
                    <a href="<?= BASE_URL ?>/seeker/assignments.php">Assignments</a>
                <?php elseif (current_role() === 'Admin'): ?>
                    <a href="<?= BASE_URL ?>/admin/users.php">Users</a>
                    <a href="<?= BASE_URL ?>/admin/exchanges.php">Exchanges</a>
                    <a href="<?= BASE_URL ?>/admin/reports.php">Reports</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/logout.php">Logout (<?= h($_SESSION['first_name']) ?>)</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php">Login</a>
                <a href="<?= BASE_URL ?>/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
    <?php endif; ?>
