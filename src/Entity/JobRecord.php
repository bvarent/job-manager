<?php

namespace Bvarent\JobManager\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * A record cq log of the status and config of some (background) job cq task.
 * Concrete child classes can add their particular config as properties.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 * 
 * @property integer $id
 * @property DateTime $start The moment this job started. (Null means it hasn't started yet.)
 * @property DateTime $lastUpdate The last time this job showed some sign of life.
 * @property integer $timeOut The maximum number of seconds this job is allowed to have gone
 *  without showing a sign of life. Before trying to kill it and regarding it as failed.
 * @property integer $pid The process id (in the scope of the OS it is running on) of this job.
 * @property boolean $success Whether this job succeeded. (1 = yes, 0 = no, stopped and failed, null = still running).
 * @property boolean $solo Whether this job is running solo. No jobs of the same type/class may be started.
 * 
 * @ORM\Entity(
 *  repositoryClass = "Bvarent\JobManager\EntityRepository\JobRecord" )
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(
 *  name = "_type",
 *  type = "string" )
 * \\ An ORM\DiscriminatorMap can't be defined here, since subclasses are defined
 *   outside the scope of this module. The map will be created automatically
 *   however by MetaDataFactory#addDefaultDiscriminatorMap.
 */
abstract class JobRecord extends Base
{

    /**
     * Whether jobs of this class should run solo by default.
     * @var boolean
     */
    protected static $soloByDefault = false;
    
    /**
     * The default time out for jobs of this class.
     * @var integer
     */
    protected static $defaultTimeOut = 300;
    
    /**
     * @return boolean
     */
    public static function getSoloByDefault()
    {
        return static::$soloByDefault;
    }
    
    /**
     * @return integer
     */
    public static function getDefaultTimeOut()
    {
        return static::$defaultTimeOut;
    }
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(
     *  type = "bigint",
     *  options = {"unsigned": true},
     *  unique = true,
     *  nullable = false )
     */
    protected $id;

    /**
     * @ORM\Column(
     *  type = "datetimetz",
     *  unique = false,
     *  nullable = true )
     */
    protected $start;

    /**
     * @ORM\Column(
     *  type = "datetimetz",
     *  unique = false,
     *  nullable = false )
     */
    protected $lastUpdate;

    /**
     * @ORM\Column(
     *  type = "integer",
     *  unique = false,
     *  nullable = false )
     */
    protected $timeOut;

    /**
     * @ORM\Column(
     *  type = "integer",
     *  unique = false,
     *  nullable = true )
     */
    protected $pid;

    /**
     * @ORM\Column(
     *  type = "boolean",
     *  unique = false,
     *  nullable = true )
     */
    protected $success = null;

    /**
     * @ORM\Column(
     *  type = "boolean",
     *  unique = false,
     *  nullable = false )
     */
    protected $solo = false;
    
    /**
     * Determines whether the job is running.
     * @return boolean
     */
    public function isRunning()
    {
        return (!is_null($this->start)
                && is_null($this->success));
    }
    
    protected function initProperties()
    {
        parent::initProperties();
        
        $this->lastUpdate = new DateTime();
        $this->timeOut = static::getDefaultTimeOut();
    }

}
