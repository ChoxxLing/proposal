<?php
$publicBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$projectBasePath = preg_replace('#/public$#', '', $publicBasePath);
$assetBasePath = ($publicBasePath === '' ? '' : $publicBasePath) . '/assets';
$apiBasePath = ($projectBasePath === '' ? '' : $projectBasePath) . '/api/index.php';
$adminUrl = ($publicBasePath === '' ? '' : $publicBasePath) . '/admin.php';
$stylesPath = __DIR__ . '/../../../public/assets/css/styles.css';
$authScriptPath = __DIR__ . '/../../../public/assets/js/auth.js';
$stylesVersion = file_exists($stylesPath) ? (string) filemtime($stylesPath) : (string) time();
$authScriptVersion = file_exists($authScriptPath) ? (string) filemtime($authScriptPath) : (string) time();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Parenting Seminar Attendance</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBasePath) ?>/css/styles.css?v=<?= htmlspecialchars($stylesVersion) ?>">
</head>
<body class="login-screen">
    <main class="login-panel">
        <div>
            <p class="eyebrow">Admin Portal</p>
            <h1>Parenting Seminar Attendance</h1>
            <p class="muted">Secure staff access for student registration, seminar management, QR attendance, SMS logs, and reports.</p>
        </div>

        <form id="loginForm" class="stack">
            <label>
                Email
                <input type="email" name="email" autocomplete="email" required>
            </label>
            <label>
                Password
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            <button type="submit" class="primary">Log in</button>
            <p id="loginMessage" class="message"></p>
        </form>
    </main>

    <script>
        window.APP_API = <?= json_encode($apiBasePath) ?>;
        window.APP_ADMIN_URL = <?= json_encode($adminUrl) ?>;
    </script>
    <script src="<?= htmlspecialchars($assetBasePath) ?>/js/auth.js?v=<?= htmlspecialchars($authScriptVersion) ?>"></script>
</body>
</html>
