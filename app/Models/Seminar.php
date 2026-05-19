<?php

class Seminar extends Model
{
    public function all(bool $includeArchived = false): array
    {
        $sql = 'SELECT * FROM seminars';
        if (!$includeArchived) {
            $sql .= " WHERE status != 'archived'";
        }
        $sql .= ' ORDER BY seminar_date DESC, start_time DESC';

        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM seminars WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function publicSchedule(): array
    {
        $result = $this->db->query(
            "SELECT *,
                CASE
                    WHEN seminar_date = CURDATE()
                        AND end_time >= CURTIME()
                    THEN 'current'
                    ELSE 'upcoming'
                END AS public_status
             FROM seminars
             WHERE status = 'scheduled'
             AND TIMESTAMP(seminar_date, end_time) >= NOW()
             ORDER BY seminar_date ASC, start_time ASC"
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function create(array $data, int $adminId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO seminars (title, description, seminar_date, start_time, end_time, venue, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'ssssssi',
            $data['title'],
            $data['description'],
            $data['seminar_date'],
            $data['start_time'],
            $data['end_time'],
            $data['venue'],
            $adminId
        );
        $stmt->execute();

        return $stmt->insert_id;
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE seminars
             SET title = ?, description = ?, seminar_date = ?, start_time = ?, end_time = ?, venue = ?, status = ?
             WHERE id = ?'
        );
        $stmt->bind_param(
            'sssssssi',
            $data['title'],
            $data['description'],
            $data['seminar_date'],
            $data['start_time'],
            $data['end_time'],
            $data['venue'],
            $data['status'],
            $id
        );
        $stmt->execute();
    }

    public function archive(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE seminars SET status = 'archived' WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public function endedScheduledIds(): array
    {
        $result = $this->db->query(
            "SELECT id
             FROM seminars
             WHERE status = 'scheduled'
             AND TIMESTAMP(seminar_date, end_time) < NOW()"
        );

        return array_column($result->fetch_all(MYSQLI_ASSOC), 'id');
    }

    public function markCompleted(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE seminars SET status = 'completed' WHERE id = ? AND status = 'scheduled'");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}
