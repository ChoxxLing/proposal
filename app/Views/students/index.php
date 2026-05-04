<section class="page" id="page-students">
    <header class="page-header">
        <div>
            <p class="eyebrow">Admin registration</p>
            <h1>Students & Parents</h1>
        </div>
        <input id="studentSearch" type="search" placeholder="Search students">
    </header>

    <div class="split">
        <section class="panel">
            <header class="panel-header"><h2>Register Student</h2></header>
            <form id="studentForm" class="grid-form">
                <input name="student_no" placeholder="Student No" required>
                <input name="first_name" placeholder="First Name" required>
                <input name="last_name" placeholder="Last Name" required>
                <input name="grade_level" placeholder="Grade Level">
                <input name="section" placeholder="Section">
                <input name="parent_name" placeholder="Parent Name" required>
                <input name="parent_phone" placeholder="Parent Phone" required>
                <button class="primary" type="submit">Save Student</button>
            </form>
            <p class="message" id="studentMessage"></p>
        </section>

        <section class="panel">
            <header class="panel-header"><h2>Bulk CSV Import</h2></header>
            <form id="csvForm" class="stack">
                <input type="file" name="csv" accept=".csv" required>
                <button class="secondary" type="submit">Import CSV</button>
            </form>
            <p class="muted">Header: student_no, first_name, last_name, grade_level, section, parent_name, parent_phone</p>
            <p class="message" id="csvMessage"></p>
        </section>
    </div>

    <section class="panel">
        <header class="panel-header"><h2>Student Records</h2></header>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Student</th><th>Parent</th><th>Phone</th><th>QR Code</th></tr></thead>
                <tbody id="studentRows"></tbody>
            </table>
        </div>
    </section>
</section>
