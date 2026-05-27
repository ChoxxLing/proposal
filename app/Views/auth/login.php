<?php
$publicBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$projectBasePath = preg_replace('#/public$#', '', $publicBasePath);
$assetBasePath = ($publicBasePath === '' ? '' : $publicBasePath) . '/assets';
$apiBasePath = ($projectBasePath === '' ? '' : $projectBasePath) . '/api/index.php';
$adminUrl = ($publicBasePath === '' ? '' : $publicBasePath) . '/admin.php';
$requestHost = $_SERVER['HTTP_HOST'] ?? '';
$requestPort = (string) ($_SERVER['SERVER_PORT'] ?? '');
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $requestPort === '443';
$isDockerHttp = str_ends_with($requestHost, ':8080') || $requestPort === '8080';

if (!$isHttps && $isDockerHttp) {
    $httpsHost = preg_replace('/:\d+$/', '', $requestHost);
    $adminUrl = 'https://' . $httpsHost . ':8443' . ($publicBasePath === '' ? '' : $publicBasePath) . '/admin.php';
}

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
            <button id="recoveryOpen" type="button" class="link-button is-hidden">Forgot account credentials?</button>
        </form>
    </main>

    <dialog id="recoveryDialog" class="recovery-dialog">
        <div class="recovery-card">
            <button id="recoveryClose" type="button" class="dialog-close" aria-label="Close account recovery">&times;</button>
            <p class="eyebrow">Account Recovery</p>
            <h2>Default login accounts</h2>
            <p class="muted">Use one of these demo accounts to access the system.</p>
            <div class="credential-list">
                <div>
                    <strong>Admin</strong>
                    <span>Email: admin@admin.com</span>
                    <span>Password: password</span>
                </div>
                <div>
                    <strong>Staff</strong>
                    <span>Email: staff@admin.com</span>
                    <span>Password: password</span>
                </div>
            </div>
        </div>
    </dialog>

    <script>
        window.APP_API = <?= json_encode($apiBasePath) ?>;
        window.APP_ADMIN_URL = <?= json_encode($adminUrl) ?>;
    </script>
    <script src="<?= htmlspecialchars($assetBasePath) ?>/js/auth.js?v=<?= htmlspecialchars($authScriptVersion) ?>"></script>
</body>
</html>
