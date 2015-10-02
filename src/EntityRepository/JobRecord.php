<?php

namespace Bvarent\JobManager\EntityRepository;

use Bvarent\JobManager\Entity;
use DateTime;
use Doctrine\ORM\EntityRepository;

/**
 * The repository for JobRecords.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
class JobRecord extends EntityRepository
{

    /**
     * Collects all currently running jobs of some kind.
     * @param string $jobClass The kind/class/type of job. Null = any kind.
     * @param boolean $solo Only consider jobs running (not-)solo. Null = either way.
     * @return Entity\JobRecord[]
     */
    public function getRunningJobs($jobClass = null, $solo = null)
    {
        // Build base query.
        $qryBuilder = $this->createQueryBuilder('j')
                ->where('j.success IS NULL');

        // Filter on job class.
        if (!empty($jobClass)) {
            $qryBuilder->andWhere('j INSTANCE OF ' . $jobClass);
        }

        // Filter on running solo.
        if (!is_null($solo)) {
            $qryBuilder->andWhere('j.solo = ' . ($solo ? 'true' : 'false'));
        }

        // Execute query.
        $qry = $qryBuilder->getQuery();
        $jobRecords = $qry->getResult();

        return $jobRecords;
    }

    /**
     * Collects all expired cq timed out jobs which are still running.
     * @param string $jobClass The kind/class/type of job. Null = any kind.
     * @return Entity\JobRecord[]
     */
    public function getTimedOutJobs($jobClass = null)
    {
        // Build query.
        $expr = $this->_em->getExpressionBuilder();
        $qryBld = $this->createQueryBuilder('j')
                ->andWhere($expr->isNull('j.success'))
                ->andWhere($expr->isNotNull('j.start'))
                ->andWhere($expr->lt("DATE_ADD(j.lastUpdate, j.timeOut, 'second')", 'CURRENT_TIMESTAMP()'));

        if ($jobClass) {
            $qryBld->andWhere('j INSTANCE OF ' . $jobClass);
        }

        // Execute query.
        $jobRecords = $qryBld->getQuery()->getResult();

        return $jobRecords;
    }

    /**
     * Collects all finished jobs that are of a certain age.
     * @param integer $ageInSeconds The minimum age (in seconds) a job record should have.
     * @param string $jobClass The kind/class/type of job. Null = any kind.
     * @return JobRecord[]
     */
    public function getOldJobs($ageInSeconds, $jobClass = null)
    {
        // Build query.
        $expr = $this->_em->getExpressionBuilder();
        $qryBld = $this->createQueryBuilder('j')
                ->where($expr->isNotNull('j.success'))
                ->andWhere($expr->lt('j.start', ':before_this_date'))
                ->setParameter('before_this_date', new DateTime("-{$ageInSeconds} seconds"));

        if ($jobClass) {
            $qryBld->andWhere('j INSTANCE OF ' . $jobClass);
        }
        
        // Execute query.
        $results = $qryBld->getQuery()->getResult();

        return $results;
    }

}
