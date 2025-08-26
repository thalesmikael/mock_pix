<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    Env::get('DB_HOST', 'localhost'),
                    Env::get('DB_PORT', '3306'),
                    Env::get('DB_DATABASE', 'pixdb')
                );

                self::$connection = new PDO(
                    $dsn,
                    Env::get('DB_USERNAME', 'root'),
                    Env::get('DB_PASSWORD', ''),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '-03:00'"
                    ]
                );
            } catch (PDOException $e) {
                throw new PDOException('Falha na conexão com o banco: ' . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
