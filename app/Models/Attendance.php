<?php

class Attendance extends Model
{
    public function checkIn(int $seminarId, int $studentId, int $adminId, string $method): array
    {
        $existing = $this->find($seminarId, $studentId);
        if ($existing) {
            return ['duplicate' => true, 'attendance' => $existing];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO attendance (seminar_id, student_id, status, check_in_method, checked_in_by, checked_in_at)
             VALUES (?, ?, 'present', ?, ?, NOW())"
        );
        $stmt->bind_param('iisi', $seminarId, $studentId, $method, $adminId);
        $stmt->execute();

        return ['duplicate' => false, 'attendance' => $this->find($seminarId, $studentId)];
    }

    public function find(int $seminarId, int $studentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, s.student_no, s.first_name, s.last_name
             FROM attendance a
             JOIN students s ON s.id = a.student_id
             WHERE a.seminar_id = ? AND a.student_id = ?
             LIMIT 1'
        );
        $stmt->bind_param('ii', $seminarId, $studentId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function recordAbsentees(int $seminarId): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO attendance (seminar_id, student_id, status, check_in_method)
             SELECT ?, s.id, 'absent', 'auto_absent'
             FROM students s
             WHERE s.is_active = 1
             AND NOT EXISTS (
                SELECT 1 FROM attendance a WHERE a.seminar_id = ? AND a.student_id = s.id
             )"
        );
        $stmt->bind_param('ii', $seminarId, $seminarId);
        $stmt->execute();

        return $stmt->affected_rows;
    }

    public function stats(?int $seminarId = null): array
    {
        $where = $seminarId ? 'WHERE seminar_id = ?' : '';
        $sql = "SELECT
                COUNT(*) total_records,
                SUM(status = 'present') present,
                SUM(status = 'absent') absent
                FROM attendance $where";

        if ($seminarId) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $seminarId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
        } else {
            $row = $this->db->query($sql)->fetch_assoc();
        }

        $totalStudents = (int) $this->db->query('SELECT COUNT(*) total FROM students WHERE is_active = 1')->fetch_assoc()['total'];
        $present = (int) ($row['present'] ?? 0);
        $absent = $seminarId ? max(0, $totalStudents - $present) : (int) ($row['absent'] ?? 0);
        $rate = $totalStudents > 0 ? round(($present / $totalStudents) * 100, 2) : 0;

        return [
            'total_students' => $totalStudents,
            'present' => $present,
            'absent' => $absent,
            'attendance_rate' => $rate,
        ];
    }

    public function report(int $seminarId): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.student_no, s.first_name, s.last_name, s.grade_level, s.section,
                    s.parent_name, s.parent_phone,
                    COALESCE(a.status, 'absent') status,
                    a.check_in_method, a.checked_in_at
             FROM students s
             LEFT JOIN attendance a ON a.student_id = s.id AND a.seminar_id = ?
             WHERE s.is_active = 1
             ORDER BY s.last_name, s.first_name"
        );
        $stmt->bind_param('i', $seminarId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
