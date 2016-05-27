<?php

namespace Bvarent\JobManager\ServiceManager;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Provides a JobManagerAware class with the JobManager service. 
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
class JobManagerInitializer implements InitializerInterface
{
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if (! $instance instanceof IJobManagerAware) {
            return;
        }
        
        $jobManager = $serviceLocator->get('Bvarent\JobManager\Service\JobManager');
        /* @param $jobManager JobManager */
        $instance->setJobManager($jobManager);
    }
}
