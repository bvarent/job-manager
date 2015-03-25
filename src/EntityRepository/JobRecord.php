<?php

namespace Bvarent\JobManager\EntityRepository;

use Bvarent\JobManager\Entity;
use Doctrine\DBAL\Types\Type as DBALType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

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
        $qryBuilder = $this->_em->createQueryBuilder()
                ->select('j')
                ->from($this->_entityName, 'j')
                ->where('j.success IS NULL');
        
        // Filter on job class.
        if (!empty($jobClass)) {
            $qryBuilder->andWhere('j INSTANCE OF :jobclass')
                    ->setParameter(':jobclass', $jobClass);
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
     * @return Entity\JobRecord[]
     */
    public function getTimedOutJobs()
    {
        // Build query.
        $qryBuilder = $this->_em->createQueryBuilder()
                ->select('j')
                ->from($this->_entityName, 'j')
                ->where('j.success IS NULL')
                ->andWhere('j.start IS NOT NULL')
                ->andWhere('DATE_ADD(j.lastUpdate, j.timeout, \'second\') < CURRENT_DATE()');
        
        // Execute query.
        $qry = $qryBuilder->getQuery();
        $jobRecords = $qry->getResult();
        
        return $jobRecords;
    }
    
    /**
     * Collects all finished jobs that are of a certain age.
     * @param integer $age The minimum age (in seconds) a job record should have.
     * @return Entity\JobRecord[]
     */
    public function getOldJobs($age)
    {
        // Build query.
        $qryBuilder = $this->_em->createQueryBuilder()
                ->select('j')
                ->from($this->_entityName, 'j')
                ->where('j.success IS NOT NULL')
                ->andWhere('j.start < DATE_SUB(CURRENT_DATE(), :age, \'second\') < ')
                ->setParameter('age', $age, DBALType::INTEGER);
        
        // Execute query.
        $qry = $qryBuilder->getQuery();
        $jobRecords = $qry->getResult();
        
        return $jobRecords;
    }

}