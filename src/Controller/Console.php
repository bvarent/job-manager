<?php

namespace Bvarent\JobManager\Controller;

use Bvarent\JobManager\Service\JobManager;
use Zend\Mvc\Controller\AbstractConsoleController;

/**
 * This controller controls actions called from the console (CLI).
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
class Console extends AbstractConsoleController
{
    /**
     * @return JobManager
     */
    public function getJobManager()
    {
        return $this->serviceLocator->get('Bvarent\JobManager\Service\JobManager');
    }
    
    /**
     * Invokes JobManager#endComaJobs.
     */
    public function endComaJobsAction()
    {
        $sendSignal = $this->params('signal');
        $jobRecordTypeOrClass = $this->params('type');
        $jobManager = $this->getJobManager();
        $endedJobsCount = $jobManager->endComaJobs($jobRecordTypeOrClass, $sendSignal);
        
        $this->console->writeLine(sprintf("Ended %d jobs.", $endedJobsCount));
    }
    
    /**
     * Invokes JobManager#deleteOldJobRecords.
     */
    public function deleteOldJobsAction()
    {
        $age = new \DateInterval($this->params('age'));
        $jobRecordTypeOrClass = $this->params('type');
        $jobManager = $this->getJobManager();
        $deletedJobsCount = $jobManager->deleteOldJobRecords($age, $jobRecordTypeOrClass);
        
        $this->console->writeLine(sprintf("Deleted %d jobs.", $deletedJobsCount));
    }
}
