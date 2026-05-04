<section class="page" id="page-reports">
    <header class="page-header">
        <div>
            <p class="eyebrow">Downloadable records</p>
            <h1>Attendance Reports</h1>
        </div>
        <div class="actions">
            <select id="reportSeminar"></select>
            <button id="downloadReport" class="primary" type="button">Download CSV</button>
        </div>
    </header>

    <section class="panel">
        <header class="panel-header"><h2>Report Preview</h2></header>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Student</th><th>Parent</th><th>Status</th><th>Method</th><th>Timestamp</th></tr></thead>
                <tbody id="reportRows"></tbody>
            </table>
        </div>
    </section>
</section>
