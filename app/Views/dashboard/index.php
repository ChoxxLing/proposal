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
            <h2>Recent Sessions</h2>
            <button id="viewAllSessions" class="ghost" type="button">View All Sessions</button>
        </header>
        <div class="session-list" id="sessionRows"></div>
    </section>
</section>
