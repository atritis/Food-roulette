<?php

namespace App;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $settings = require __DIR__ . '/../config/settings.php';
            $db = $settings['db'];

            self::$instance = new PDO(
                "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset={$db['charset']}",
                $db['username'],
                $db['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$instance;
    }
}
