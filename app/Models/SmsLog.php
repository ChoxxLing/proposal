<?php

class SmsLog extends Model
{
    public function create(int $seminarId, int $studentId, string $phone, string $message, string $status, string $response): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO sms_logs (seminar_id, student_id, phone, message, status, provider_response)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iissss', $seminarId, $studentId, $phone, $message, $status, $response);
        $stmt->execute();
    }

    public function recent(): array
    {
        return $this->db->query(
            'SELECT l.*, s.first_name, s.last_name, sem.title
             FROM sms_logs l
             JOIN students s ON s.id = l.student_id
             JOIN seminars sem ON sem.id = l.seminar_id
             ORDER BY l.sent_at DESC
             LIMIT 100'
        )->fetch_all(MYSQLI_ASSOC);
    }
}
