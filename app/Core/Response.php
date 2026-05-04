<?php

class Response
{
    public static function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    public static function error(Throwable $exception, int $status = 500): void
    {
        ErrorHandler::log($exception);
        self::json(ErrorHandler::payload($exception), $status);
    }
}
