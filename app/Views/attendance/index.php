<section class="page" id="page-attendance">
    <header class="page-header">
        <div>
            <p class="eyebrow">Real-time check-in</p>
            <h1>QR Attendance</h1>
        </div>
        <select id="attendanceSeminar"></select>
    </header>

    <div class="split">
        <section class="panel">
            <header class="panel-header">
                <h2>Camera Scanner</h2>
                <button id="startScanner" class="secondary" type="button">Start</button>
            </header>
            <video id="scannerVideo" playsinline muted></video>
            <canvas id="scannerCanvas" class="is-hidden"></canvas>
            <p id="scanStatus" class="message scanner-status">
                Select a seminar, then start the camera. On a phone, open this page with HTTPS using your computer IP address.
            </p>
        </section>

        <section class="panel">
            <header class="panel-header"><h2>Manual Check-in</h2></header>
            <form id="manualForm" class="stack">
                <select name="student_id" id="manualStudent"></select>
                <button class="primary" type="submit">Verify Check-in</button>
            </form>

            <form id="tokenForm" class="stack">
                <input name="qr_token" placeholder="Paste QR token">
                <button class="secondary" type="submit">Submit Token</button>
            </form>

            <button id="closeSeminar" class="danger" type="button">Record Absentees</button>
            <p id="manualMessage" class="message"></p>
        </section>
    </div>
</section>
