<?php

class StudentController
{
    public function index(): void
    {
        Auth::requireLogin();
        Response::json(['students' => (new Student())->all(trim($_GET['search'] ?? ''))]);
    }

    public function store(): void
    {
        Auth::requireRole(['admin']);

        $required = ['student_no', 'first_name', 'last_name', 'parent_name', 'parent_phone'];
        foreach ($required as $field) {
            if (trim($_POST[$field] ?? '') === '') {
                Response::json(['error' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'], 422);
            }
        }

        try {
            $id = (new Student())->create([
                'student_no' => trim($_POST['student_no']),
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'batch_num' => trim($_POST['batch_num'] ?? ''),
                'section' => trim($_POST['section'] ?? ''),
                'parent_name' => trim($_POST['parent_name']),
                'parent_phone' => trim($_POST['parent_phone']),
            ], Auth::user()['id']);

            Response::json(['success' => true, 'id' => $id]);
        } catch (Throwable $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function import(): void
    {
        Auth::requireRole(['admin']);

        if (!isset($_FILES['csv'])) {
            Response::json(['error' => 'CSV file is required.'], 422);
        }

        $result = (new Student())->importCsv($_FILES['csv']['tmp_name'], Auth::user()['id']);
        Response::json(['success' => true, 'result' => $result]);
    }
}
