<?php

namespace Bvarent\JobManager;

return [
    'aliases' => [
        'JobManager' => __NAMESPACE__ . '\Service\JobManager',
        __NAMESPACE__ => __NAMESPACE__ . '\Service\JobManager',
    ],
    'factories' => [
        __NAMESPACE__ . '\Service\JobManager' => __NAMESPACE__ . '\ServiceManager\JobManagerFactory',
    ],
    'initializers' => [
        __NAMESPACE__ . '\ServiceManager\JobManagerInitializer',
        __NAMESPACE__ . '\ServiceManager\EntityManagerInitializer',
    ]
];
