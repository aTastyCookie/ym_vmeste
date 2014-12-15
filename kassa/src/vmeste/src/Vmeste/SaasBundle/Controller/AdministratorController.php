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
use Symfony\Component\HttpFoundation\Request;
use Vmeste\SaasBundle\Entity\SysEvent;

class AdministratorController extends Controller
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
        if($sysEventVal != NULL && $sysEventVal == 'LOGIN') {

            $this->trackAdminLoginEvent();
            $session->remove('SYS_EVENT');

            $currentUser = $this->get('security.context')->getToken()->getUser();

            $session->set('RECENT_SYS_USER', array(
                'userId' => $currentUser->getId(),
                'event' => SysEvent::ADMIN_LOGOUT,
                'ip' => $request->getClientIp(),
            ));
        }

        $connection = $this->getDoctrine()->getConnection();
        $queryBuilder = $connection->createQueryBuilder();

        $queryBuilder
            ->select('yandex_kassa.id')
            ->from('yandex_kassa', 'yandex_kassa')
            ->leftJoin('yandex_kassa', 'settings', null, 'settings.yk_id = yandex_kassa.id')
            ->where('settings.id is null');
        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll();
        $unusedKassa = count($result);

        return array('unusedKassa' => $unusedKassa);
    }

    public function kassadestroyAction()
    {
        $em = $this->getDoctrine()->getManager();
        $connection = $this->getDoctrine()->getConnection();
        $queryBuilder = $connection->createQueryBuilder();

        $queryBuilder
            ->select('yandex_kassa.id')
            ->from('yandex_kassa', 'yandex_kassa')
            ->leftJoin('yandex_kassa', 'settings', null, 'settings.yk_id = yandex_kassa.id')
            ->where('settings.id is null');
        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll();
        foreach($result as $kassa_id) {
            $kassa = $em->getRepository('Vmeste\SaasBundle\Entity\YandexKassa')
                ->findOneBy(array('id' => $kassa_id['yandex_kassa.id']));
            if($kassa) {
                $em->remove($kassa);
            }
        }
        $em->flush();
        $redirectUrl = $this->generateUrl('admin_home');
        return $this->redirect($redirectUrl);
    }

    public function trackAdminLoginEvent()
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $sysEvent = new SysEvent();
        $sysEvent->setUserId($user->getId());
        $sysEvent->setEvent(SysEvent::ADMIN_LOGIN);
        $sysEvent->setIp($this->container->get('request')->getClientIp());

        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);
    }
} 