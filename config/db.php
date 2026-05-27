<?php

class DB
{
    private static function env(string $key, string $default): string
    {
        $value = getenv($key);

        return $value === false ? $default : $value;
    }

    public static function connect(): mysqli
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $db = new mysqli(
            self::env('DB_HOST', 'localhost'),
            self::env('DB_USER', 'root'),
            self::env('DB_PASS', ''),
            self::env('DB_NAME', 'parenting_seminar')
        );
        $db->set_charset('utf8mb4');

        return $db;
    }
}
