<?php

namespace Bvarent\JobManager\ServiceManager;

use Doctrine\ORM\EntityManager;

/**
 * An implementer of this interface will be provided with the JobManager module's
 *  configured EntityManager, if the implementer is created by the ServiceManager.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
interface IEntityManagerAware
{

    /**
     * Accepts the EntityManager service.
     *
     * @param EntityManager $entitymanager
     */
    public function setEntityManager(EntityManager $entitymanager);

    /**
     * Returns the EntityManager service.
     *
     * @return EntityManager
     */
    public function getEntityManager();
}
