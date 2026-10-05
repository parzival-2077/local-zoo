<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Capsule\Manager as Capsule;
use PDO;

final class Database
{
    private static bool $booted = false;

    public static function bootEloquent(): void
    {
        if (self::$booted) {
            return;
        }

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'   => 'pgsql',
            'host'     => getenv('DB_HOST') ?: 'postgres',
            'port'     => getenv('DB_PORT') ?: '5432',
            'database' => getenv('DB_NAME') ?: 'app',
            'username' => getenv('DB_USER') ?: 'app',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        self::$booted = true;
    }

    public static function connect(): PDO
    {
        $host = getenv('DB_HOST') ?: 'postgres';
        $port = getenv('DB_PORT') ?: '5432';
        $name = getenv('DB_NAME') ?: 'app';
        $user = getenv('DB_USER') ?: 'app';
        $password = getenv('DB_PASSWORD') ?: '';

        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $name);

        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 3,
        ]);
    }
}