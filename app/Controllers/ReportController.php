<?php

class ReportController
{
    public function csv(): void
    {
        Auth::requireLogin();
        $seminarId = (int) ($_GET['seminar_id'] ?? 0);
        $status = strtolower(trim($_GET['status'] ?? ''));
        $status = in_array($status, ['present', 'absent'], true) ? $status : null;
        $rows = (new Attendance())->report($seminarId, $status);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="attendance-report.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Student No', 'First Name', 'Last Name', 'Batch No.', 'Section', 'Parent', 'Phone', 'Status', 'Method', 'Timestamp']);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['student_no'],
                $row['first_name'],
                $row['last_name'],
                $row['batch_num'],
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
