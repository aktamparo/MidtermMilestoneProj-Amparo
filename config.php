<?php
declare(strict_types=1);
// Update these values if your XAMPP MySQL settings differ.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'barangay_kusina';
const DB_USER = 'root';
const DB_PASS = '';

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            self::$connection = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$connection;
    }
}

function db(): PDO
{
    return Database::connection();
}
