<?php

require_once __DIR__ . '/../app/bootstrap.php';

$resource = $_GET['resource'] ?? '';
$action = $_GET['action'] ?? 'index';

try {
    match ($resource) {
        'auth' => match ($action) {
            'login' => (new AuthController())->login(),
            'logout' => (new AuthController())->logout(),
            'me' => (new AuthController())->me(),
            default => Response::json(['error' => 'Unknown auth action.'], 404),
        },
        'dashboard' => (new DashboardController())->stats(),
        'students' => match ($action) {
            'index' => (new StudentController())->index(),
            'store' => (new StudentController())->store(),
            'import' => (new StudentController())->import(),
            default => Response::json(['error' => 'Unknown student action.'], 404),
        },
        'seminars' => match ($action) {
            'index' => (new SeminarController())->index(),
            'store' => (new SeminarController())->store(),
            'update' => (new SeminarController())->update(),
            'archive' => (new SeminarController())->archive(),
            'send_sms' => (new SeminarController())->sendSms(),
            default => Response::json(['error' => 'Unknown seminar action.'], 404),
        },
        'attendance' => match ($action) {
            'qr' => (new AttendanceController())->checkInQr(),
            'manual' => (new AttendanceController())->manual(),
            'close' => (new AttendanceController())->close(),
            'report' => (new AttendanceController())->report(),
            default => Response::json(['error' => 'Unknown attendance action.'], 404),
        },
        'reports' => match ($action) {
            'csv' => (new ReportController())->csv(),
            default => Response::json(['error' => 'Unknown report action.'], 404),
        },
        default => Response::json(['error' => 'Unknown resource.'], 404),
    };
} catch (Throwable $e) {
    Response::error($e);
}
