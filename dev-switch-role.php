<?php
/**
 * Development-only role switcher for OKR testing.
 * Sets a session override for the okr grade so pages/backend calls simulate
 * a different staff.grade without touching the database.
 *
 * This file must NOT be deployed to production.
 */
date_default_timezone_set('Asia/Kuala_Lumpur');

if (session_id() == '') {
    session_start();
}

// Restrict to localhost only. REMOTE_ADDR is the real connecting IP (set by
// the server, not the client); the Host header alone is client-controlled and
// could be spoofed on production ("Host: localhost"), so both must agree.
// Same check as navbar.php's dev grade bar - keep the two in sync.
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
$hostOnly   = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
$isLocal    = in_array($remoteAddr, ['127.0.0.1', '::1'], true)
    && in_array($hostOnly, ['localhost', '127.0.0.1', '[::1]'], true);

if (!$isLocal) {
    http_response_code(403);
    echo 'This endpoint is not available in production.';
    exit;
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed.';
    exit;
}

$allowed_roles = array(0, 1, 2, 3, 4, 5);
$role_input    = isset($_POST['role']) ? $_POST['role'] : null;

if ($role_input === 'clear') {
    unset($_SESSION['okr_dev_role_override']);
} elseif ($role_input !== null) {
    $role = (int)$role_input;
    if (in_array($role, $allowed_roles)) {
        $_SESSION['okr_dev_role_override'] = $role;
    }
}

$redirect = isset($_POST['redirect']) && $_POST['redirect'] !== ''
    ? $_POST['redirect']
    : '/odb/okr/index.php';

// Only allow same-site absolute paths ("/odb/..."), to prevent an open
// redirect - rejects full URLs ("https://x"), protocol-relative ("//x") and
// backslash variants ("/\x") that browsers also treat as another host.
if (!preg_match('#^/(?![/\\\\])#', $redirect) || strpos($redirect, '://') !== false
    || preg_match('/[\r\n]/', $redirect)) {
    $redirect = '/odb/okr/index.php';
}

header('Location: ' . $redirect);
exit;
