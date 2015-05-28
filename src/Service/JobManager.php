<?php

namespace Bvarent\JobManager\Service;

use Bvarent\JobManager\Entity\JobRecord;
use Bvarent\JobManager\EntityRepository\JobRecord as JobRecordRepo;
use Bvarent\JobManager\ServiceManager\IEntityManagerAware;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use RuntimeException;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * Manager of background jobs cq tasks.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
class JobManager implements ServiceManagerAwareInterface, IEntityManagerAware
{

    const JOB_BASE_CLASS = 'Bvarent\JobManager\Entity\JobRecord';

    /**
     * The global ZF2 Service Manager.
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * The Doctrine Entity Manager which manages our JobRecord entities.
     * @var EntityManager
     */
    protected $entityManager;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Starts a job, only if no conflicting jobs were found. By checking
     *  that no other instances are running solo. Or are running at all, if this
     *  one should run solo.
     * @param JobRecord $jobRecord The job to start.
     * @internal The job was optimistically created already, but not started.
     *  Now it is checked for solo-ness. If not, it's deleted again. Worst case,
     *  multiple concurrent job managers create a new job and none of them
     *  eventually keeps one.
     * @todo Use (multi-platform) pessimistic locking for creating a job.
     * @throws RuntimeException When a conflicting job is found.
     */
    public function startJob($jobRecord)
    {
        $em = $this->entityManager;
        $jobRepo = $em->getRepository(static::JOB_BASE_CLASS);
        /* @var $jobRepo JobRecordRepo */
        
        // Optimistically persist the job, but in a not-started state. This is
        //  for a concurrent job manager to detect it, so it can be regarded as
        //  a conflicting job.
        $jobRecord->start = null;
        $em->persist($jobRecord);
        $em->flush($jobRecord);

        // Find jobs of this class, running solo (or any if this job needs solo), 
        //  excluding this job itself.
        // TODO $this->killComaJobs() first?;
        $conflictingJobs = $jobRepo->getRunningJobs(get_class($jobRecord), ($jobRecord->solo ? null : true));
        if (false !== $pos = array_search($jobRecord, $conflictingJobs)) {
            unset($conflictingJobs[$pos]);
        }

        // If a conflicting job was found, end this job and throw an exception.
        if (!empty($conflictingJobs)) {
            $this->finishJob($jobRecord, false);
            $firstRunningJob = current($conflictingJobs);
            /* @var $firstRunningJob JobRecord */
            throw new RuntimeException(sprintf('A job of type %s is already running %s since %s with pid %s.', $jobRecord, $firstRunningJob->start, ($firstRunningJob->solo ? '(solo)' : ''), $firstRunningJob->pid));
        }

        // Otherwise officially start this job.
        $jobRecord->start = new DateTime();
        $em->persist($jobRecord);
        $em->flush($jobRecord);
    }

    /**
     * Creates a new instance of (a subclass of) JobRecord.
     * @param string $jobClass The type/class (FQCN) of job to run.
     * @param boolean $runSolo Should the job run solo? I.a.w. no other jobs of
     *  the same class/type may be running.
     * @param integer $timeOut The max number of seconds the job may run without
     *  showing a sign of life.
     * @return JobRecord
     * @throws InvalidArgumentException For an invalid job class.
     */
    public function getNewJob($jobClass, $runSolo = null, $timeOut = null)
    {
        $em = $this->entityManager;
        
        // Check args.
        if (!is_a($jobClass, static::JOB_BASE_CLASS, true)) {
            throw new InvalidArgumentException('Job class/type should be a (descendant of) ' . static::JOB_BASE_CLASS);
        }
        if (is_null($runSolo)) {
            $runSolo = $jobClass::getSoloByDefault();
        }
        if (is_null($timeOut)) {
            $timeOut = $jobClass::getDefaultTimeOut();
        } else {
            $timeOut = (int) $timeOut;
        }

        // Create new JobRecord
        $newJob = new $jobClass();
        /* @var $newJob JobRecord */
        $newJob->solo = !!$runSolo;
        if (is_int($timeOut)) {
            $newJob->timeOut = $timeOut;
        }

        return $newJob;
    }

    /**
     * Records a sign of life from some job.
     * @param JobRecord $jobRecord
     */
    public function showSignOfLife(JobRecord $jobRecord)
    {
        $em = $this->entityManager;

        $jobRecord->lastUpdate = new DateTime();
        $em->persist($jobRecord);
        $em->flush($jobRecord);
    }

    /**
     * Records the ending of a job.
     * @param JobRecord $jobRecord
     * @param boolean $succes
     */
    public function finishJob(JobRecord $jobRecord, $succes = true)
    {
        $em = $this->entityManager;

        $jobRecord->lastUpdate = new DateTime();
        $jobRecord->success = !!$succes;
        $em->persist($jobRecord);
        $em->flush($jobRecord);
    }

    /**
     * Tries to end, maybe even kill, jobs that have timed out and record those
     *  as having failed.
     * @todo It would be not cool if other processes (or this) with the same
     *  (perhaps re-used) pid were killed.
     * @return integer The number of ended jobs.
     */
    public function killComaJobs()
    {
        $em = $this->entityManager;
        $jobRepo = $em->getRepository(static::JOB_BASE_CLASS);
        /* @var $jobRepo JobRecordRepo */
        
        $comaJobs = $jobRepo->getTimedOutJobs();
        foreach ($comaJobs as $comaJob) {
            // TODO exec("kill -9 $comaJob->pid");
            
            // Mark as a failure.
            $comaJob->success = false;
            $em->persist($comaJob);
        }
        $em->flush();
        
        return count($comaJobs);
    }

    /**
     * Removes logs of jobs that are no longer running and are older than a
     *  certain age.
     * @param integer $age The minimum age (in seconds) a job record should have to
     *  be deleted.
     * @return integer Number of jobs deleted.
     */
    public function deleteOldJobRecords(DateInterval $age)
    {
        $em = $this->entityManager;
        $jobRepo = $em->getRepository(static::JOB_BASE_CLASS);
        /* @var $jobRepo JobRecordRepo */
        
        $oldJobs = $jobRepo->getOldJobs($age);
        foreach ($oldJobs as $oldJob) {
            $em->remove($oldJob);
        }
        $em->flush();
    }

}
