<?php

class AttendanceController
{
    public function checkInQr(): void
    {
        Auth::requireLogin();
        $seminarId = (int) ($_POST['seminar_id'] ?? 0);
        $token = trim($_POST['qr_token'] ?? '');

        if ($seminarId <= 0 || $token === '') {
            Response::json(['error' => 'Seminar and QR token are required.'], 422);
        }

        $student = (new Student())->findByQrToken($token);
        if (!$student) {
            Response::json(['error' => 'QR code is not registered.'], 404);
        }

        $result = (new Attendance())->checkIn($seminarId, (int) $student['id'], Auth::user()['id'], 'qr');
        Response::json(['success' => true, 'duplicate' => $result['duplicate'], 'student' => $student, 'attendance' => $result['attendance']]);
    }

    public function manual(): void
    {
        Auth::requireLogin();
        $seminarId = (int) ($_POST['seminar_id'] ?? 0);
        $studentId = (int) ($_POST['student_id'] ?? 0);

        if ($seminarId <= 0 || $studentId <= 0) {
            Response::json(['error' => 'Seminar and student are required.'], 422);
        }

        $result = (new Attendance())->checkIn($seminarId, $studentId, Auth::user()['id'], 'manual');
        Response::json(['success' => true, 'duplicate' => $result['duplicate'], 'attendance' => $result['attendance']]);
    }

    public function close(): void
    {
        Auth::requireRole(['admin']);
        $seminarId = (int) ($_POST['seminar_id'] ?? 0);
        $count = (new Attendance())->recordAbsentees($seminarId);
        Response::json(['success' => true, 'absentees_recorded' => $count]);
    }

    public function report(): void
    {
        Auth::requireLogin();
        $seminarId = (int) ($_GET['seminar_id'] ?? 0);
        $status = $this->statusFilter($_GET['status'] ?? '');
        Response::json(['rows' => (new Attendance())->report($seminarId, $status)]);
    }

    private function statusFilter(string $status): ?string
    {
        $status = strtolower(trim($status));
        return in_array($status, ['present', 'absent'], true) ? $status : null;
    }
}
