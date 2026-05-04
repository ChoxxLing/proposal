<?php

class DB
{
    private const HOST = 'localhost';
    private const USER = 'root';
    private const PASS = '';
    private const NAME = 'parenting_seminar';

    public static function connect(): mysqli
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $db = new mysqli(self::HOST, self::USER, self::PASS, self::NAME);
        $db->set_charset('utf8mb4');

        return $db;
    }
}
