<?php

namespace Setfive\Gearman;

use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;

class Logger {

    private static $logger = null;

    public static function getLogger(){

        if( self::$logger ){
            return self::$logger;
        }

        self::$logger = new Monolog('gearman');
        self::$logger->pushHandler(new \Monolog\Handler\ErrorLogHandler());

        return self::$logger;
    }

}