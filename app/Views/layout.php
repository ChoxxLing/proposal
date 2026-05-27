<?php
$publicBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$projectBasePath = preg_replace('#/public$#', '', $publicBasePath);
$assetBasePath = ($publicBasePath === '' ? '' : $publicBasePath) . '/assets';
$apiBasePath = ($projectBasePath === '' ? '' : $projectBasePath) . '/api/index.php';
$adminUrl = ($publicBasePath === '' ? '' : $publicBasePath) . '/admin.php';
$publicUrl = ($publicBasePath === '' ? '' : $publicBasePath) . '/index.php';
$requestHost = $_SERVER['HTTP_HOST'] ?? '';
$requestPort = (string) ($_SERVER['SERVER_PORT'] ?? '');
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $requestPort === '443';
$isDockerHttp = str_ends_with($requestHost, ':8080') || $requestPort === '8080';

if (!$isHttps && $isDockerHttp) {
    $httpsHost = preg_replace('/:\d+$/', '', $requestHost);
    $adminUrl = 'https://' . $httpsHost . ':8443' . ($publicBasePath === '' ? '' : $publicBasePath) . '/admin.php';
}

$stylesPath = __DIR__ . '/../../public/assets/css/styles.css';
$qrScriptPath = __DIR__ . '/../../public/assets/js/vendor/jsqr.min.js';
$appScriptPath = __DIR__ . '/../../public/assets/js/app.js';
$stylesVersion = file_exists($stylesPath) ? (string) filemtime($stylesPath) : (string) time();
$qrScriptVersion = file_exists($qrScriptPath) ? (string) filemtime($qrScriptPath) : (string) time();
$appScriptVersion = file_exists($appScriptPath) ? (string) filemtime($appScriptPath) : (string) time();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parenting Seminar Attendance</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBasePath) ?>/css/styles.css?v=<?= htmlspecialchars($stylesVersion) ?>">
</head>
<body>
    <aside class="sidebar">
        <div class="brand">
            <span>PS</span>
            <div>
                <strong>Parenting Seminar</strong>
                <small>Attendance System</small>
            </div>
        </div>
        <nav>
            <button class="nav-link active" data-page="dashboard">Dashboard</button>
            <button class="nav-link" data-page="students">Students & Parents</button>
            <button class="nav-link" data-page="seminars">Seminars</button>
            <button class="nav-link" data-page="attendance">QR Attendance</button>
            <button class="nav-link" data-page="reports">Reports</button>
        </nav>
        <div class="account">
            <strong><?= htmlspecialchars($admin['name']) ?></strong>
            <small><?= htmlspecialchars(ucfirst($admin['role'])) ?></small>
            <button id="logoutBtn" class="ghost">Log out</button>
        </div>
    </aside>

    <main class="app">
        <?php require __DIR__ . '/dashboard/index.php'; ?>
        <?php require __DIR__ . '/students/index.php'; ?>
        <?php require __DIR__ . '/seminars/index.php'; ?>
        <?php require __DIR__ . '/attendance/index.php'; ?>
        <?php require __DIR__ . '/reports/index.php'; ?>
    </main>

    <dialog id="logoutDialog" class="confirm-dialog">
        <div class="confirm-card">
            <p class="eyebrow">Confirm Log Out</p>
            <h2>Log out of this account?</h2>
            <p class="muted">You will return to the public landing page.</p>
            <div class="confirm-actions">
                <button id="cancelLogout" type="button" class="secondary">Cancel</button>
                <button id="confirmLogout" type="button" class="danger">Log out</button>
            </div>
        </div>
    </dialog>

    <script>
        window.APP_USER = <?= json_encode($admin) ?>;
        window.APP_API = <?= json_encode($apiBasePath) ?>;
        window.APP_ADMIN_URL = <?= json_encode($adminUrl) ?>;
        window.APP_PUBLIC_URL = <?= json_encode($publicUrl) ?>;
    </script>
    <script src="<?= htmlspecialchars($assetBasePath) ?>/js/vendor/jsqr.min.js?v=<?= htmlspecialchars($qrScriptVersion) ?>"></script>
    <script src="<?= htmlspecialchars($assetBasePath) ?>/js/app.js?v=<?= htmlspecialchars($appScriptVersion) ?>"></script>
</body>
</html>
