<?php

namespace Bvarent\JobManager;

return array(
    'aliases' => array(
        'JobManager' => __NAMESPACE__ . '\Service\JobManager',
        __NAMESPACE__ => __NAMESPACE__ . '\Service\JobManager',
    ),
    'factories' => array(
        __NAMESPACE__ . '\Service\JobManager' => __NAMESPACE__ . '\ServiceManager\JobManagerFactory',
    ),
    'initializers' => array(
        __NAMESPACE__ . '\ServiceManager\JobManagerInitializer',
        __NAMESPACE__ . '\ServiceManager\EntityManagerInitializer',
    )
);