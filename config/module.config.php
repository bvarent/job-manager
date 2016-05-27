<?php

namespace Bvarent\JobManager;

return [
    Module::CONFIG_KEY => Options\JobManager::defaults(),
    
    // Controllers.
    'controllers' => [
        'invokables' => [
            __NAMESPACE__ . '\Controller\Console' => __NAMESPACE__ . '\Controller\Console',
        ],
    ],
    
    // Console routes.
    'console' => [
        'router' => [
            'routes' => [
                Module::CONFIG_KEY . ' end-coma-jobs' => [
                    'options' => [
                        'route' => Module::CONFIG_KEY . ' end-coma-jobs [--signal=] [<type>]',
                        'defaults' => [
                            'controller' => __NAMESPACE__ . '\Controller\Console',
                            'action' => 'endComaJobs',
                        ],
                    ],
                ],
                Module::CONFIG_KEY . ' delete-old-jobs' => [
                    'options' => [
                        'route' => Module::CONFIG_KEY . ' delete-old-jobs --age= [<type>]',
                        'defaults' => [
                            'controller' => __NAMESPACE__ . '\Controller\Console',
                            'action' => 'deleteOldJobs',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
