<?php

namespace Bvarent\JobManager\ServiceManager;

use Bvarent\JobManager\Module;
use Bvarent\JobManager\Options;
use Bvarent\JobManager\Service\JobManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory to build an instance of the JobManager service. Reading the config
 *  options from ZF2's config.
 */
class JobManagerFactory implements FactoryInterface
{
    /**
     * {inheritdoc}
     * @return JobManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $zf2Config = $serviceLocator->get('config');
        $jmConfig = $zf2Config[Module::CONFIG_KEY];
        $options = new Options\JobManager($jmConfig);
        /* @var $options Options\JobManager */

        $entityManager = $serviceLocator->get('doctrine.entitymanager.' . $options->entitymanager);

        $jobManager = new JobManager($options, $entityManager);

        return $jobManager;
    }
}
