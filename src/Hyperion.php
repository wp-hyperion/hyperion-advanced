<?php

namespace Hyperion;

use Hyperion\Core\ClassTreeMapper;
use Hyperion\Core\MainEngine;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Hyperion
{
    public const ADD_MODULE_EVENT = 'addModule';
    public const ADD_COMPONENT_EVENT = 'addComponent';
    public const ADD_CLASSTREEMAPPERPATH_EVENT = 'add_classtreemapperpath_event';
    public const LOAD_CONTAINER_EVENT = 'loadContainer';

    private static Logger $monolog;

    public static function poweringUp()
    {
        self::$monolog = new Logger('logbook');
        self::$monolog->pushHandler(new StreamHandler(__DIR__."/../logbooks/app.log", Logger::DEBUG));
    }

    public static function shuttingDown()
    {

    }

    public static function ignition()
    {
        $engineModules = new MainEngine();
        $classTreeMapper = new ClassTreeMapper();
        do_action(self::ADD_MODULE_EVENT, $engineModules);
        do_action(self::ADD_COMPONENT_EVENT, $engineModules);
        do_action(self::ADD_CLASSTREEMAPPERPATH_EVENT, $classTreeMapper);
        do_action(self::LOAD_CONTAINER_EVENT, $classTreeMapper);
    }
}