<?php

namespace Bva\JobManager;

return array(
    'aliases' => array(
        'JobManager' => __NAMESPACE__ . '\Service\JobManager',
    ),
    'invokables' => array(
        __NAMESPACE__ . '\Service\JobManager' => __NAMESPACE__ . '\Service\JobManager',
    ),
    'initializers' => array(
        __NAMESPACE__ . '\ServiceManager\JobManagerInitializer',
        __NAMESPACE__ . '\ServiceManager\EntityManagerInitializer',
    )
);