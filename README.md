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

Require with composer and add the module to the Zend application config.

Configuration
-------------

### User Configuration

The top-level configuration key for user configuration of this module is `jobmanager`.

#### Key: `entitymanager`

The `entitymanager` key is used for specifying the name of the Doctrine EntityManager
to use. That key will be acquired by asking the ZF2 ServiceManager for
`doctrine.entitymanager.<value>`. E.g.: `orm_default`.

ZF2 Services
------------

### Bvarent\JobManager\Service\JobNanager alias JobManager

Access the `JobManager` from within your job to register it and keep the manager updated.

TODO
----

* Testing.
* Ability to kill timed out jobs.
* Pessimistic locking when creating a solo job.
* Web pages with job status summary, etc.
* Move Entity\Base to external lib.