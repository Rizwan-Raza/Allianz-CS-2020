<?php

class DB
{
    private static $server = "localhost";
    private static $username = "root";
    private static $password = "";
    private static $dbname = "all-cs";

    public static function getConnection()
    {
        return new mysqli(self::$server, self::$username, self::$password, self::$dbname, "3306");
    }
}