<?php
require_once __DIR__ . '/includes/session-init.php';
$_SESSION = [];

// Hapus cookie sesi di browser juga, bukan cuma data di server — supaya
// tidak ada jejak sesi akun sebelumnya yang tersisa di browser ini.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();
header('Location: login.php');
exit;
