<?php

namespace Bvarent\JobManager;

// $modulePath should be already defined by the file which included this file.
/* @var $modulePath string */
if (!isset($modulePath)) {
    // Defaults.
    $modulePath = dirname(__DIR__);
}

// $doctrineServiceNames should be already defined by the file which included this file.
if (!isset($doctrineServiceNames)) {
    // Defaults.
    $doctrineServiceNames = array();
    $doctrineServiceNames['driver'] = 'orm_default';    
}

// $config['doctrine'] = ...
return array(
    'driver' => array(
        // Configure our own metadata driver, which basically means:
        //  read class metadata from annotations on files in this path.
        __NAMESPACE__ . '_driver' => array(
            'cache' => 'array',
            'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
            'paths' => $modulePath . '/src/Entity'
        ),
        // Presuming the existing metadata driver in the ORM service is a DriverChain, add our module-specific driver to it.
        $doctrineServiceNames['driver'] => array(
            'drivers' => array(
                __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
            )
        )
    ),
);
