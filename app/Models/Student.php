<?php

class Student extends Model
{
    public function all(string $search = ''): array
    {
        if ($search !== '') {
            $like = '%' . $search . '%';
            $stmt = $this->db->prepare(
                'SELECT * FROM students
                 WHERE student_no LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR parent_name LIKE ?
                 ORDER BY last_name, first_name'
            );
            $stmt->bind_param('ssss', $like, $like, $like, $like);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        return $this->db->query('SELECT * FROM students ORDER BY last_name, first_name')->fetch_all(MYSQLI_ASSOC);
    }

    public function create(array $data, int $adminId): int
    {
        $qrToken = bin2hex(random_bytes(16));
        $stmt = $this->db->prepare(
            'INSERT INTO students
             (student_no, first_name, last_name, batch_num, section, parent_name, parent_phone, qr_token, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'ssssssssi',
            $data['student_no'],
            $data['first_name'],
            $data['last_name'],
            $data['batch_num'],
            $data['section'],
            $data['parent_name'],
            $data['parent_phone'],
            $qrToken,
            $adminId
        );
        $stmt->execute();

        return $stmt->insert_id;
    }

    public function importCsv(string $path, int $adminId): array
    {
        $handle = fopen($path, 'r');
        $created = 0;
        $skipped = 0;
        $errors = [];

        if (!$handle) {
            return ['created' => 0, 'skipped' => 0, 'errors' => ['Unable to read uploaded CSV.']];
        }

        $header = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== false) {
            if (!$header || count($header) !== count($row)) {
                $skipped++;
                $errors[] = 'Invalid CSV row column count.';
                continue;
            }

            $data = array_combine($header ?: [], $row);
            if (!$data || empty($data['student_no'])) {
                $skipped++;
                continue;
            }

            try {
                $this->create([
                    'student_no' => trim($data['student_no']),
                    'first_name' => trim($data['first_name'] ?? ''),
                    'last_name' => trim($data['last_name'] ?? ''),
                    'batch_num' => trim($data['batch_num'] ?? ''),
                    'section' => trim($data['section'] ?? ''),
                    'parent_name' => trim($data['parent_name'] ?? ''),
                    'parent_phone' => trim($data['parent_phone'] ?? ''),
                ], $adminId);
                $created++;
            } catch (Throwable $e) {
                $skipped++;
                $errors[] = ($data['student_no'] ?? 'row') . ': ' . $e->getMessage();
            }
        }

        fclose($handle);

        return ['created' => $created, 'skipped' => $skipped, 'errors' => $errors];
    }

    public function findByQrToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM students WHERE qr_token = ? AND is_active = 1 LIMIT 1');
        $stmt->bind_param('s', $token);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }
}
