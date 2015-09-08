<?php

namespace Bvarent\JobManager;

return array(
    Module::CONFIG_KEY => array(
    // The name of the Doctrine EntityManager service to lift upon.
        'entitymanager' => 'orm_default',
    ),
    
    // Controllers.
    'controllers' => array(
        'invokables' => array(
            __NAMESPACE__ . 'Controller\Console' => __NAMESPACE__ . 'Controller\Console',
        ),
    ),
    
    // Console routes.
    'console' => array(
        'router' => array(
            'routes' => array(
                Module::CONFIG_KEY . ' end-coma-jobs' => array(
                    'options' => array(
                        Module::CONFIG_KEY . ' end-coma-jobs [--signal=] [<type>]',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . 'Controller\Console',
                            'action' => 'endComaJobs',
                        ),
                    ),
                ),
                Module::CONFIG_KEY . ' delete-old-jobs' => array(
                    'options' => array(
                        Module::CONFIG_KEY . ' delete-old-jobs --age= [<type>]',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . 'Controller\Console',
                            'action' => 'deleteOldJobs',
                        ),
                    ),
                ),
            ),
        ),
    ),
);