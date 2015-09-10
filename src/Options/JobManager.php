<?php

namespace Bvarent\JobManager\Options;

use InvalidArgumentException;
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

    protected $endComaJobsOnInit;
    
    protected function getEndComaJobsOnInit()
    {
        return $this->endComaJobsOnInit;
    }

    protected function setEndComaJobsOnInit($val)
    {
        if (!(is_boolean($val)
                ||  is_string($val)
                || (is_array($val) && (count($val) === array_sum(array_map('is_string', $val))))) ) {
            throw new InvalidArgumentException(sprintf("'end_coma_jobs_on_init should be a bool, string or string[]"));
        }
        $this->endComaJobsOnInit = $val;
    }

    protected $endComaJobsOnInitSig;
    
    protected function getEndComaJobsOnInitSig()
    {
        return $this->endComaJobsOnInitSig;
    }

    protected function setEndComaJobsOnInitSig($val)
    {
        $this->endComaJobsOnInitSig = max(0, (int) $val);
    }
}
