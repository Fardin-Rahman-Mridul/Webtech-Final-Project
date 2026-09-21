<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// BASE_URL works out the project's own URL path automatically, so all
// links/CSS/JS work whether the project sits at the domain root
// (http://localhost/) or inside a subfolder (http://localhost/skill-exchange-platform/).
if (!defined('BASE_URL')) {
    $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\'));
    $projectRoot = str_replace('\\', '/', rtrim(dirname(__DIR__), '/\\'));
    $base = substr($projectRoot, strlen($docRoot));
    define('BASE_URL', $base === false ? '' : $base);
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Redirect to login if not authenticated. Call at the top of any protected page.
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

// Redirect away if the logged-in user's role doesn't match. Call after require_login().
function require_role($roleName) {
    require_login();
    if ($_SESSION['role_name'] !== $roleName) {
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_role() {
    return $_SESSION['role_name'] ?? null;
}
