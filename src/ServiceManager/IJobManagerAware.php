<?php

namespace Bvarent\JobManager\ServiceManager;

use Bvarent\JobManager\Service\JobManager;

/**
 * An implementer of this interface will be provided with the JobManager service
 *  if the implementer is created by the ServiceManager.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
interface IJobManagerAware
{
    /**
     * Accepts the JobManager service.
     *
     * @param JobManager $jobManager
     */
    public function setJobManager(JobManager $jobManager);

    /**
     * Returns the JobManager service.
     *
     * @return JobManager
     */
    public function getJobManager();
}
