<?php

declare(strict_types=1);

session_start();

$appConfig = require __DIR__ . '/../config/app.php';

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/Core/ErrorHandler.php';
require_once __DIR__ . '/Core/Model.php';
require_once __DIR__ . '/Core/Auth.php';
require_once __DIR__ . '/Core/Response.php';

ErrorHandler::register($appConfig);

require_once __DIR__ . '/Services/SmsService.php';
require_once __DIR__ . '/Models/Admin.php';
require_once __DIR__ . '/Models/Student.php';
require_once __DIR__ . '/Models/Seminar.php';
require_once __DIR__ . '/Models/Attendance.php';
require_once __DIR__ . '/Models/SmsLog.php';
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Controllers/DashboardController.php';
require_once __DIR__ . '/Controllers/StudentController.php';
require_once __DIR__ . '/Controllers/SeminarController.php';
require_once __DIR__ . '/Controllers/AttendanceController.php';
require_once __DIR__ . '/Controllers/ReportController.php';

date_default_timezone_set('Asia/Manila');
