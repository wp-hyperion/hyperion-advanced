<?php

namespace Hyperion\Interfaces;

interface PluginInterface
{
    public static function poweringUp();
    public static function shuttingDown();
    public static function ignition();
}