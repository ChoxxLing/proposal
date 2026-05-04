<?php

abstract class Model
{
    protected mysqli $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }
}
