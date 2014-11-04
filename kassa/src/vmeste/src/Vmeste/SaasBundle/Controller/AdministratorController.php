<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/14/14
 * Time: 5:05 PM
 */

namespace Vmeste\SaasBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Vmeste\SaasBundle\Entity\SysEvent;

class AdministratorController extends Controller
{

    /**
     * @Template()
     */
    public function homeAction()
    {

//        $user = $this->get('security.context')->getToken()->getUser();
//
//        $sysEvent = new SysEvent();
//        $sysEvent->setUserId($user->getId());
//        $sysEvent->setEvent("TEST_EVENT"); //TODO Move events to SysEvents constants
//        $sysEvent->setIp($this->container->get('request')->getClientIp());
//
//        $eventTracker = $this->get('sys_event_tracker');
//        $eventTracker->track($sysEvent);

        return array();
    }
} 