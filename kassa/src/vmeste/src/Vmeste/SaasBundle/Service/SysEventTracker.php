<?php

namespace Vmeste\SaasBundle\Service;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Vmeste\SaasBundle\Entity\SysEvent;

/**
 * Created by PhpStorm.
 * User: Eugene
 * Date: 03.11.2014
 * Time: 6:34
 */
class SysEventTracker
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function track(SysEvent $event)
    {
        if ($event != null) {
            try {
                $this->em->persist($event);
                $this->em->flush();
            } catch (\Exception $e) {
                $this->logger->critical("EventTracker: Can't save event info\n" . $e->getCode() . " " . $e->getMessage());
            }
        }

    }


} 