<?php

class DashboardController
{
    public function stats(): void
    {
        Auth::requireLogin();
        $this->closeEndedSeminars();

        $seminarId = isset($_GET['seminar_id']) && $_GET['seminar_id'] !== '' ? (int) $_GET['seminar_id'] : null;

        Response::json([
            'stats' => (new Attendance())->stats($seminarId),
            'seminars' => (new Seminar())->all(),
            'sessions' => (new Attendance())->recentSessions(),
        ]);
    }

    private function closeEndedSeminars(): void
    {
        $seminars = new Seminar();
        $attendance = new Attendance();

        foreach ($seminars->endedScheduledIds() as $id) {
            $attendance->recordAbsentees((int) $id);
            $seminars->markCompleted((int) $id);
        }
    }
}
