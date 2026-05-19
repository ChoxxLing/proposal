<?php

require_once __DIR__ . '/../app/bootstrap.php';

$seminars = (new Seminar())->publicSchedule();

require __DIR__ . '/../app/Views/public/landing.php';
