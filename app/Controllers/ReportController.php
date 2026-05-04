<?php

class ReportController
{
    public function csv(): void
    {
        Auth::requireLogin();
        $seminarId = (int) ($_GET['seminar_id'] ?? 0);
        $rows = (new Attendance())->report($seminarId);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="attendance-report.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Student No', 'First Name', 'Last Name', 'Grade', 'Section', 'Parent', 'Phone', 'Status', 'Method', 'Timestamp']);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['student_no'],
                $row['first_name'],
                $row['last_name'],
                $row['grade_level'],
                $row['section'],
                $row['parent_name'],
                $row['parent_phone'],
                $row['status'],
                $row['check_in_method'],
                $row['checked_in_at'],
            ]);
        }
        fclose($out);
        exit;
    }
}
