<?php

require_once __DIR__ . '/../app/bootstrap.php';

$admin = Auth::user();

if (!$admin) {
    require __DIR__ . '/../app/Views/auth/login.php';
    exit;
}

require __DIR__ . '/../app/Views/layout.php';
