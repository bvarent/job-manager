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

    /**
     * Gives the path to the root directory of this module.
     * @return string
     */
    protected function getModulePath()
    {
        // Assume this file is in {module root path}/src.
        return dirname(__DIR__);
    }
    
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
    
    /**
     * Determines the doctrine service names to use from the module's config.
     * @param Config $totalConfig
     * @return Map<string, string> [service_type => service_name]
     */
    protected function getDoctrineServiceNames(Config $totalConfig)
    {
        // Get the entitymanager's name to use from the config.
        $entityManagerName = $totalConfig[static::CONFIG_KEY]['entitymanager'];
        $servicenames = array();
        $servicenames['entitymanager'] = $entityManagerName;
        $servicenames['connection'] = $totalConfig['doctrine']['entitymanager'][$entityManagerName]['connection'];
        $servicenames['configuration'] = $totalConfig['doctrine']['entitymanager'][$entityManagerName]['configuration'];
        $servicenames['driver'] = $totalConfig['doctrine']['configuration'][$servicenames['configuration']]['driver'];
        
        return $servicenames;
    }

    /**
     * Gets the configuration extensions for the Doctrine (ORM) Module.
     * @param Config $totalConfig A config object which contains [module_config_key]['doctrine_service_names'].
     * @return Config
     */
    protected function getDoctrineConfig(Config $totalConfig)
    {
        $modulePath = $this->getModulePath();
        
        // Get doctrine services names to use. This variable is used by the included config file.
        $doctrineServiceNames = $this->getDoctrineServiceNames($totalConfig);

        $doctrineConfigArray = include $modulePath . '/config/doctrine.config.php';
        $doctrineConfig = new Config(array('doctrine' => $doctrineConfigArray));
        
        return $doctrineConfig;
    }
    
    /**
     * Merges our Doctrine config extensions into the existing total config.
     * @param Config $totalConfig The total config after merging by the ModuleManager.
     */
    protected function mergeInDoctrineConfig(Config & $totalConfig)
    {
        $doctrineConfig = $this->getDoctrineConfig($totalConfig);
        $totalConfig->merge($doctrineConfig);
    }
    
    /**
     * Performs actions upon the total config, when the ConfigListener is done merging.
     * @param ModuleEvent $e
     */
    public function onMergeConfig(ModuleEvent $e)
    {
        // Retrieve the config.
        $configListener = $e->getConfigListener();
        $totalConfig         = $configListener->getMergedConfig();
        /* @var $totalConfig Config */
        
        // Perform custom actions.
        $this->mergeInDoctrineConfig($totalConfig);

        // Pass the changed configuration back to the listener.
        $configListener->setMergedConfig($totalConfig->toArray());
    }

    public function init(ModuleManagerInterface $moduleManager)
    {
        // Bind our 'onMergeConfig' method to the 'mergeConfig' event.
        $events = $moduleManager->getEventManager();
        $events->attach(ModuleEvent::EVENT_MERGE_CONFIG, array($this, 'onMergeConfig'));
    }

}
