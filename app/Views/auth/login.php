<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Parenting Seminar Attendance</title>
    <link rel="stylesheet" href="assets/css/styles.css">
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

    <script src="assets/js/auth.js"></script>
</body>
</html>
