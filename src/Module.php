<?php

namespace Bvarent\JobManager;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{
    /**
     * The key to use in the global ZF2 config to identify this module.
     */
    const CONFIG_KEY = 'bvarent-jobmanager';

    public function getAutoloaderConfig()
    {
        // Composer probably takes care of the autoloading. But just in case:
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include dirname(__DIR__) . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return include dirname(__DIR__) . '/config/service.config.php';
    }

}
