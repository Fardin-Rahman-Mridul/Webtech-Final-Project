<?php
// Escape output safely for HTML
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Simple flash-message helper using the session
function set_flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function profile_image_url($userId) {
    $directory = __DIR__ . '/../uploads/profile-pictures';
    $matches = glob($directory . '/user_' . (int) $userId . '.*');

    if (!empty($matches)) {
        return BASE_URL . '/uploads/profile-pictures/' . basename($matches[0]) . '?v=' . filemtime($matches[0]);
    }

    return 'https://picsum.photos/seed/studyswap-user-' . (int) $userId . '/160/160';
}

function store_uploaded_file($file, $directoryName, $allowedTypes, $maxSize = 10485760) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Please choose a file to upload.'];
    }

    if ($file['size'] > $maxSize) {
        return ['error' => 'The file must be 10 MB or smaller.'];
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowedTypes[$mimeType])) {
        return ['error' => 'This file type is not supported.'];
    }

    $directory = __DIR__ . '/../uploads/' . $directoryName;
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $storedName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
    $storedPath = $directory . '/' . $storedName;
    if (!move_uploaded_file($file['tmp_name'], $storedPath)) {
        return ['error' => 'The file could not be saved. Please try again.'];
    }

    return [
        'path' => 'uploads/' . $directoryName . '/' . $storedName,
        'name' => basename($file['name']),
    ];
}
