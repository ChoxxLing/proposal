<?php

require_once __DIR__ . '/../app/bootstrap.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$admin = Auth::user();

if (!$admin) {
    require __DIR__ . '/../app/Views/auth/login.php';
    exit;
}

require __DIR__ . '/../app/Views/layout.php';
