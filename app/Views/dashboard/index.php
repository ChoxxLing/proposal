<section class="page active" id="page-dashboard">
    <header class="page-header">
        <div>
            <p class="eyebrow">Live overview</p>
            <h1>Dashboard</h1>
        </div>
        <select id="dashboardSeminar"></select>
    </header>

    <div class="metric-grid">
        <article class="metric"><span>Total Students</span><strong id="metricTotal">0</strong></article>
        <article class="metric"><span>Present</span><strong id="metricPresent">0</strong></article>
        <article class="metric"><span>Absent</span><strong id="metricAbsent">0</strong></article>
        <article class="metric"><span>Attendance Rate</span><strong id="metricRate">0%</strong></article>
    </div>

    <section class="panel">
        <header class="panel-header">
            <h2>Recent SMS Delivery Logs</h2>
        </header>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Seminar</th><th>Parent Phone</th><th>Status</th><th>Sent At</th></tr></thead>
                <tbody id="smsLogRows"></tbody>
            </table>
        </div>
    </section>
</section>
