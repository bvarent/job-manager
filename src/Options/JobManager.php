<?php

namespace Bvarent\JobManager\Options;

use InvalidArgumentException;
use Zend\Stdlib\AbstractOptions;

/**
 * Options for \Bvarent\JobManager\Service\JobManager
 * 
 * @property string $entitymanager Name of the doctrine entitymanager service to use. The X in doctrine.entitymanager.X.
 * @property boolean|string|string[] $end_coma_jobs_on_init End coma (timed out) jobs upon initializing the Job Manager. If a(n array of) string(s) is given, only that jobrecord type will be ended.
 * @property int $end_coma_jobs_on_init_sig If configured to end coma jobs upon init, also send those jobs this interrupt signal.
 */
class JobManager extends AbstractOptions
{
    public static function defaults()
    {
        return [
            'entitymanager' => 'orm_default',
            'end_coma_jobs_on_init' => false,
            'end_coma_jobs_on_init_sig' => null,
        ];
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
        if (!(is_bool($val)
                ||  is_string($val)
                || (is_array($val) && (count($val) === array_sum(array_map('is_string', $val)))))) {
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
        $this->endComaJobsOnInitSig = is_null($val) ? null : max(0, (int) $val);
    }
}
