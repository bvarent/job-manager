<?php

namespace Bvarent\JobManager\ServiceManager;

use Bvarent\JobManager\Service\JobManager;

/**
 * Implements IJobManagerAware
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
trait TJobManagerAware
{
    /**
     * @var JobManager
     */
    protected $jobManager;
    
    public function setJobManager(JobManager $jobManager)
    {
        $this->jobManager = $jobManager;
    }

    public function getJobManager()
    {
        return $this->jobManager;
    }
}
