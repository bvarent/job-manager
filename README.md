Job Manager
===========

Utility to manage and keep record of background php jobs.

Features
--------

* It's a Zend Framework 2 module.
* A job registers itself with the manager.
* The manager keeps track of the jobs.

Installation, requirements
--------------------------

* Require with composer
* Add the module to the Zend application config. After DoctrineModule and DoctrineORMModule.
* Create your own JobRecord descendant(s). Integrate the JobManager in your jobs.
* Configure, create and apply doctrine migrations for your DB.

Example usage
-------------

```php
<?php

namespace MyNameSpace;

use Bvarent\JobManager\Service\JobManager;
use Bvarent\JobManager\Entity\JobRecord;
use Doctrine\ORM\Mapping as ORM;
use Zend\Config\Config;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ModuleManager\ModuleEvent;

/**
 * @property string $myCustomValue Some custom value to be recorded for a job.
 * @ORM\Entity
 */
class MyJobRecord extends JobRecord {
	protected static $soloByDefault = true;
	
	/**
     * @ORM\Column(type = "string")
     */
	protected $myCustomValue;
}

class Module {
	public function init(ModuleManagerInterface $moduleManager) {
		$moduleManager->getEventManager()->attach(ModuleEvent::EVENT_MERGE_CONFIG, array($this, 'mergeInJobRecordDoctrineConfig'));
        return 1;
	}
	/**
	 * Merges into ZF2's config, the Doctrine annotation driver for MyJobRecord Entity.
	 */
	public function mergeInJobRecordDoctrineConfig(ModuleEvent $e) {
		$configListener = $e->getConfigListener();
		$totalConfig = $configListener->getMergedConfig();
		
		// Find out JobManager's Annotation Driver
		$jobManagerEntitymanagerName = $totalConfig[\Bvarent\JobManager\Module::CONFIG_KEY]['entitymanager'];
		$jobManagerConfigurationName = $totalConfig['doctrine']['entitymanager'][$jobManagerEntitymanagerName]['configuration'];
		$jobManagerDriverName = $totalConfig['doctrine']['configuration'][$jobManagerConfigurationName]['driver'];
		
		// Hook our annotation driver onto that of the JobManager.
		$totalConfig->merge(new Config([
		'doctrine' => ['driver' => [
			// Configure our own metadata driver, which basically means: read class metadata from annotations on files in this path.
			__NAMESPACE__ . '_jobm_driver' => [
				'cache' => 'array',
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'paths' => __DIR__, // FIXME
			]],
			// Presuming the existing metadata driver in the ORM service is a DriverChain, add our driver to its list of drivers.
			$jobManagerDriverName => ['drivers' => [__NAMESPACE__ . '\Job\Entity' => __NAMESPACE__ . '_jobm_driver']]
		]]));
		$configListener->setMergedConfig($totalConfig->toArray());
	}
}

class MyJob {
	public function __construct(JobManager $jobManager) {
		$this->jobManager = $jobManager;
	}
	
	public function invoke() {
		// Register the start of the job.
        $jobRecord = $this->jobManager->getNewJob(MyJobRecord::class);
        $jobRecord->myCustomValue = 'something';
        $this->jobManager->startJob($jobRecord);
		
		// Do our job.
		for ($i=0; $i<10; $i++) {
			sleep(1);
			$this->jobManager->showSignOfLife($jobRecord);
		}
		
		// Finish up.
		$this->jobManager->finishJob($jobRecord);
	}
}
```

Configuration
-------------

### User Configuration

The top-level configuration key for user configuration of this module is `bvarent-jobmanager`.

#### Key: `entitymanager`

The `entitymanager` key is used for specifying the name of the Doctrine EntityManager
to use. That key will be acquired by asking the ZF2 ServiceManager for
`doctrine.entitymanager.<value>`. E.g.: `orm_default`.

ZF2 Services
------------

### Bvarent\JobManager\Service\JobManager alias JobManager

Access the `JobManager` from within your job to register the job and keep the manager updated.

TODO
----

* Testing.
* Ability to kill timed out jobs.
* Pessimistic locking when creating a solo job.
* Web pages with job status summary, etc.
* Job scheduling.
* Doctrine migrations workflow.
* Move Entity\Base to external lib.