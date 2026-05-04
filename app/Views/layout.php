<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parenting Seminar Attendance</title>
    <link rel="stylesheet" href="assets/css/styles.css">
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

    <script>
        window.APP_USER = <?= json_encode($admin) ?>;
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>
