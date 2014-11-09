<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/14/14
 * Time: 5:03 PM
 */

namespace Vmeste\SaasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Vmeste\SaasBundle\Entity\SysEvent;

class CustomerController extends Controller
{

    /**
     * @Template()
     * @param Request $request
     * @return array
     */
    public function homeAction(Request $request)
    {

        $session = $request->getSession();
        $sysEventVal = $session->get('SYS_EVENT', NULL);
        if ($sysEventVal != NULL && $sysEventVal == 'LOGIN') {

            $this->trackCustomerLoginEvent();
            $session->remove('SYS_EVENT');

            $currentUser = $this->get('security.context')->getToken()->getUser();

            $session->set('RECENT_SYS_USER', array(
                'userId' => $currentUser->getId(),
                'event' => SysEvent::CUSTOMER_LOGOUT,
                'ip' => $request->getClientIp(),
            ));
        }

        return array();
    }


    public function trackCustomerLoginEvent()
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $sysEvent = new SysEvent();
        $sysEvent->setUserId($user->getId());
        $sysEvent->setEvent(SysEvent::CUSTOMER_LOGIN);
        $sysEvent->setIp($this->container->get('request')->getClientIp());

        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);
    }

} 