<?php

namespace Hyperion;

use Hyperion\Doctrine\Service\DoctrineService;
use Hyperion\Loader\Collection\RegisteredModuleCollection;
use Hyperion\Loader\HyperionLoader;

class Hyperion
{
    private DoctrineService $doctrineService;

    public function __construct(DoctrineService $doctrineService)
    {
        $this->doctrineService = $doctrineService;
    }

    public static function init()
    {
        add_action(HyperionLoader::REGISTER_HYPERION_MODULE, function (RegisteredModuleCollection $registeredModuleCollection) {
            $registeredModuleCollection->addModule(__NAMESPACE__);
        }, 1);
    }

    public static function shuttingDown()
    {
    }

    public function ignition()
    {
        die('ok');
    }
}