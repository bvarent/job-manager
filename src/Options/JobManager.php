<?php

namespace Bvarent\JobManager\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Options for \Bvarent\JobManager\Service\JobManager
 * 
 * @property string $entitymanager Name of the doctrine entitymanager service to use. The X in doctrine.entitymanager.X.
 */
class JobManager extends AbstractOptions
{
    public static function defaults() {
        return array(
            'entitymanager' => 'orm_default',
        );
    }

    protected $entitymanager;
    
    protected function getEntitymanager()
    {
        return $this->entitymanager;
    }

    protected function setEntitymanager($val)
    {
        $this->entitymanager = (string) $val;
    }
}
