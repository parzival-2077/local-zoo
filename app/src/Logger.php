<?php

declare(strict_types=1);

namespace App;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as MonologLogger;

final class Logger
{
    private static ?MonologLogger $logger = null;

    public static function get(): MonologLogger
    {
        if (self::$logger === null) {
            self::$logger = new MonologLogger('local-zoo');
            self::$logger->pushHandler(
                new StreamHandler(__DIR__ . '/../logs/app.log', Level::Debug)
            );
        }

        return self::$logger;
    }
}