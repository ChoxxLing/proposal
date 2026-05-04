<section class="page" id="page-seminars">
    <header class="page-header">
        <div>
            <p class="eyebrow">Events</p>
            <h1>Seminar Management</h1>
        </div>
    </header>

    <section class="panel">
        <header class="panel-header"><h2>Create or Edit Seminar</h2></header>
        <form id="seminarForm" class="grid-form">
            <input type="hidden" name="id">
            <input name="title" placeholder="Title" required>
            <input name="seminar_date" type="date" required>
            <input name="start_time" type="time" required>
            <input name="end_time" type="time" required>
            <input name="venue" placeholder="Venue" required>
            <select name="status">
                <option value="scheduled">Scheduled</option>
                <option value="completed">Completed</option>
                <option value="archived">Archived</option>
            </select>
            <textarea name="description" placeholder="Description"></textarea>
            <button class="primary" type="submit">Save Seminar</button>
        </form>
        <p class="message" id="seminarMessage"></p>
    </section>

    <section class="panel">
        <header class="panel-header"><h2>Seminar Events</h2></header>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Title</th><th>Schedule</th><th>Venue</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="seminarRows"></tbody>
            </table>
        </div>
    </section>
</section>
