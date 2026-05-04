<?php

class SeminarController
{
    public function index(): void
    {
        Auth::requireLogin();
        Response::json(['seminars' => (new Seminar())->all(true)]);
    }

    public function store(): void
    {
        Auth::requireRole(['admin']);
        $data = $this->payload();
        $id = (new Seminar())->create($data, Auth::user()['id']);
        $this->notifyParents($id);

        Response::json(['success' => true, 'id' => $id]);
    }

    public function update(): void
    {
        Auth::requireRole(['admin']);
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            Response::json(['error' => 'Seminar ID is required.'], 422);
        }

        (new Seminar())->update($id, $this->payload());
        Response::json(['success' => true]);
    }

    public function archive(): void
    {
        Auth::requireRole(['admin']);
        $id = (int) ($_POST['id'] ?? 0);
        (new Seminar())->archive($id);
        Response::json(['success' => true]);
    }

    public function sendSms(): void
    {
        Auth::requireRole(['admin']);
        $id = (int) ($_POST['id'] ?? 0);
        $sent = $this->notifyParents($id);

        Response::json(['success' => true, 'sent' => $sent]);
    }

    private function payload(): array
    {
        foreach (['title', 'seminar_date', 'start_time', 'end_time', 'venue'] as $field) {
            if (trim($_POST[$field] ?? '') === '') {
                Response::json(['error' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'], 422);
            }
        }

        return [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description'] ?? ''),
            'seminar_date' => trim($_POST['seminar_date']),
            'start_time' => trim($_POST['start_time']),
            'end_time' => trim($_POST['end_time']),
            'venue' => trim($_POST['venue']),
            'status' => trim($_POST['status'] ?? 'scheduled'),
        ];
    }

    private function notifyParents(int $seminarId): int
    {
        $seminar = (new Seminar())->find($seminarId);
        if (!$seminar) {
            Response::json(['error' => 'Seminar not found.'], 404);
        }

        $students = (new Student())->all();
        $sms = new SmsService();
        $logs = new SmsLog();
        $sent = 0;

        foreach ($students as $student) {
            if (trim($student['parent_phone']) === '') {
                continue;
            }

            $message = sprintf(
                'Parenting seminar: %s on %s, %s-%s at %s.',
                $seminar['title'],
                $seminar['seminar_date'],
                $seminar['start_time'],
                $seminar['end_time'],
                $seminar['venue']
            );
            $result = $sms->send($student['parent_phone'], $message);
            $logs->create($seminarId, (int) $student['id'], $student['parent_phone'], $message, $result['status'], $result['provider_response']);
            $sent++;
        }

        return $sent;
    }
}
