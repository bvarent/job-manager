<?php

namespace Bvarent\JobManager\ServiceManager;

use Doctrine\ORM\EntityManager;
use Zend\Config\Config;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Adds our configured EntityManager to an a service.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
class EntityManagerInitializer implements InitializerInterface
{

    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if (! $instance instanceof IEntityManagerAware) {
            return;
        }
        
        // Retrieve our EntityManager.
        $config = $serviceLocator->get('config');
        /* @var $config Config */
        $entityManagerServiceName = $config[\Bvarent\JobManager\Module::CONFIG_KEY]['entitymanager'];
        $em = $serviceLocator->get('doctrine.entitymanager.' . $entityManagerServiceName);
        /* @var $em EntityManager */
        
        // Inject the EntityManager into the service.
        $instance->setEntityManager($em);
    }

}
