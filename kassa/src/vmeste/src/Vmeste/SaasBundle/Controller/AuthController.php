<?php

namespace Vmeste\SaasBundle\Controller;

use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Vmeste\SaasBundle\Entity\Ip;
use Vmeste\SaasBundle\Entity\RecoverPassword;
use Vmeste\SaasBundle\Entity\RecoverToken;
use Vmeste\SaasBundle\Entity\Role;
use Vmeste\SaasBundle\Entity\User;
use Vmeste\SaasBundle\Entity\SysEvent;
use Vmeste\SaasBundle\Util\Hash;


class AuthController extends Controller
{

    const NON_ACTIVE_TOKEN = 0;

    const ACTIVE_TOKEN = 1;

    const SYS_EVENT = 'SYS_EVENT';

    const RECENT_SYS_USER = 'RECENT_SYS_USER';

    public function loginAction()
    {

        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                return $this->redirect($this->generateUrl('admin_home'));
            } else if ($this->get('security.context')->isGranted('ROLE_USER')) {
                return $this->redirect($this->generateUrl('customer_home'));
            }
        }

        $request = $this->getRequest();

        $session = $request->getSession();
        $this->trackLogoutSysEvent($session);

        $clientIp = $request->getClientIp();
        $requestAllowed = $this->bruteForceCheck($clientIp);
        if (!$requestAllowed)
            return $this->redirect($this->generateUrl('forgot_pass'));

        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContextInterface::AUTHENTICATION_ERROR
            );
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);

        return $this->render(
            'VmesteSaasBundle:Auth:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error' => $error,
            )
        );
    }

    public function securityCheckAction()
    {

    }

    /**
     * @Template()
     */

    public function forgotPasswordAction(Request $request)
    {

        $logger = $this->get('logger');

        $successMessage = NULL;
        $errorMessage = NULL;

        if ($request->isMethod('POST')) {

            if($_SESSION['token'] == $request->request->get('token')) {
                $_SESSION['token'] = '';
            } else {
                die("404 Not Found!");
            }

            $email = $request->request->get('email');
            $emailConstraint = new Email();

            $emailConstraint->message = "The email " . $email . ' is not a valid email';

            $errorList = $this->get('validator')->validateValue($email, $emailConstraint);

            if (count($errorList) == 0) {

                $user = $this->getDoctrine()
                    ->getRepository('Vmeste\SaasBundle\Entity\User')
                    ->findOneBy(array('email' => $email));

                if ($user != NULL) {

                    $em = $this->getDoctrine()->getManager();
                    $queryBuilder = $em->createQueryBuilder();

                    $disablePreviousActiveTokensQuery = $queryBuilder
                        ->update('Vmeste\SaasBundle\Entity\RecoverToken', 'rt')
                        ->set('rt.active', '?1')
                        ->where('rt.userId = ?2')
                        ->andWhere('rt.active = ?3')
                        ->setParameter(1, self::NON_ACTIVE_TOKEN)
                        ->setParameter(2, $user->getId())
                        ->setParameter(3, self::ACTIVE_TOKEN)
                        ->getQuery();


//                    $logger->info('[RECOVER_EMAIL] Disabled tokens SQL query: ' . $disablePreviousActiveTokensQuery->getSql());

                    $disabledTokenNum = $disablePreviousActiveTokensQuery->execute();

//                    $logger->info('[RECOVER_EMAIL] Disabled tokens: ' . $disabledTokenNum . ' for user: ' . $user->getEmail());

                    $recoverTokenHash = Hash::generateRecoverToken();

                    $recoverToken = new RecoverToken();
                    $recoverToken->setUserId($user->getId());
                    $recoverToken->setToken($recoverTokenHash);

                    $dbManager = $this->getDoctrine()->getManager();
                    $dbManager->persist($recoverToken);
                    $dbManager->flush();

                    $emailFrom = $this->container->getParameter('pass.recover.email.from');
                    $route = $this->container->getParameter('pass.recover.url');

                    $recoverUri = $this->generateUrl($route);
                    $recoverUriWithToken = $this->getRequest()->getHost() . $recoverUri . "/" . $recoverTokenHash;

//                    $logger->info('[RECOVER_EMAIL] From:' . $emailFrom . ' To: ' . $user->getEmail() . ' Recover url: ' . $recoverUriWithToken);

                    $message = \Swift_Message::newInstance()
                        ->setSubject('Password recover on Vmeste')
                        ->setFrom($emailFrom)
                        ->setTo($user->getEmail())
                        ->setBody(
                            $this->renderView(
                                'VmesteSaasBundle:Email:recoverEmail.html.twig',
                                array('name' => $user->getEmail(), 'link' => $recoverUriWithToken)
                            )
                        );
                    $this->get('mailer')->send($message);

                    $successMessage = 'Recover letter has been sent on your email!';

                    $sysEvent = new SysEvent();
                    $sysEvent->setUserId($user->getId());
                    $sysEvent->setEvent(SysEvent::RECOVER_PASSWORD);
                    $sysEvent->setIp($this->container->get('request')->getClientIp());
                    $eventTracker = $this->get('sys_event_tracker');
                    $eventTracker->track($sysEvent);

                } else {
                    $errorMessage = 'User with email ' . $email . ' doesn\'t exists!';
                    $token = md5(uniqid(mt_rand() . microtime()));
                    $_SESSION['token'] = $token;
                }
            } else {
                $errorMessage = $errorList[0]->getMessage();
                $token = md5(uniqid(mt_rand() . microtime()));
                $_SESSION['token'] = $token;
            }
        } else {
            $token = md5(uniqid(mt_rand() . microtime()));
            $_SESSION['token'] = $token;
        }

        return array(
            'error' => $errorMessage,
            'success' => $successMessage,
            'token' => $token
        );
    }

    /**
     * @Template()
     */
    public function recoverPasswordAction($token = NULL)
    {

        $errorMessage = null;
        $userId = null;
        $hideForm = false;
        $errorMessageArray = array();

        $logger = $this->get('logger');

        $request = $this->get('request');

        if ($request->isMethod('POST')) {

            $token = $request->request->get('token');
            $password = $request->request->get('password');
            $passwordRepeat = $request->request->get('password_repeat');

            $notBlank = new NotBlank();

            $validator = $this->get('validator');

            $notBlank->message = "Token doesn't exists!";
            $tokenErrorList = $validator->validateValue($token, $notBlank);
            $notBlank->message = "Password is empty!";
            $passwordErrorList = $validator->validateValue($password, $notBlank);
            $notBlank->message = "You hadn't repeated your password!";
            $passwordRepeatErrorList = $validator->validateValue($passwordRepeat, $notBlank);
            $passwordsEqual = ($password === $passwordRepeat);

            if (count($tokenErrorList) == 0 && count($passwordErrorList) == 0
                && count($passwordRepeatErrorList) == 0 && $passwordsEqual
            ) {

                $doctrine = $this->getDoctrine();
                $em = $doctrine->getManager();

                $recoverToken = $em->getRepository('Vmeste\SaasBundle\Entity\RecoverToken')
                    ->findOneBy(array('token' => $token, 'active' => self::ACTIVE_TOKEN));

                if ($recoverToken == null) {
                    array_push($errorMessageArray, "Token doesn't exist!");
                    $hideForm = true;
                } else {
                    $userId = $recoverToken->getUserId();

                    $user = $this->getDoctrine()
                        ->getRepository('Vmeste\SaasBundle\Entity\User')
                        ->findOneBy(array('id' => $userId));

                    if ($user != null) {

                        $factory = $this->get('security.encoder_factory');
                        $encoder = $factory->getEncoder($user);
                        $password = $encoder->encodePassword($password, $user->getSalt());

                        $user->setPassword($password);
                        $recoverToken->setActive(0);

                        $em->persist($user);
                        $em->persist($recoverToken);
                        $em->flush();

                        $sysEvent = new SysEvent();
                        $sysEvent->setUserId($userId);
                        $sysEvent->setEvent(SysEvent::RECOVER_PASSWORD);
                        $sysEvent->setIp($this->container->get('request')->getClientIp());
                        $eventTracker = $this->get('sys_event_tracker');
                        $eventTracker->track($sysEvent);

                        $logger->info('[CHANGE_PASSWORD] For user with id: ' . $userId);

                        return $this->redirect($this->generateUrl("login"));

                    } else {
                        $userNotFoundErrorMessage = "User not found!";
                        array_push($errorMessageArray, $userNotFoundErrorMessage);
                    }
                }

            } else {

                if (count($tokenErrorList) != 0)
                    array_push($errorMessageArray, $tokenErrorList[0]->getMessage());

                if (count($passwordErrorList) != 0)
                    array_push($errorMessageArray, $passwordErrorList[0]->getMessage());

                if (count($passwordRepeatErrorList) != 0)
                    array_push($errorMessageArray, $passwordRepeatErrorList[0]->getMessage());

                if (!$passwordsEqual)
                    array_push($errorMessageArray, "Passwords are not equal!");
            }
        }

        return array('errors' => $errorMessageArray, 'token' => $token, 'hideForm' => $hideForm);
    }

    /**
     * @Template()
     */
    public function updatePasswordAction()
    {
        return array();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    public function getEntityManager()
    {
        $em = $this->getDoctrine()->getManager();
        return $em;
    }

    /**
     * @param $em
     * @param $clientIp
     */
    public function getClientIpFromDatabase($em, $clientIp)
    {
        return $em->getRepository('Vmeste\SaasBundle\Entity\Ip')->findOneBy(array('ip' => $clientIp));
    }

    /**
     * @param $clientIp
     * @param $em
     */
    public function remeberNewIP($clientIp, $em)
    {
        $newClientIp = new Ip();
        $newClientIp->setIp($clientIp);
        $newClientIp->setState(Ip::PENDING);
        $newClientIp->setAttempt(0);
        $em->persist($newClientIp);
        $em->flush();
    }

    /**
     * @param $ipTime
     * @return int
     */
    public function getElapsedTimeAfterBlock($ipTime)
    {
        $currentDatetime = new \DateTime();
        $timeElapsed = $currentDatetime->getTimestamp() - $ipTime;
        return $timeElapsed;
    }

    /**
     * @param $clientIp
     * @return bool
     */
    public function bruteForceCheck($clientIp)
    {

        $requestAllowed = true;

        $ipBlockTimeSec = intval($this->container->getParameter('security.login.ip_block_time_sec'));

        $em = $this->getEntityManager();
        $ip = $this->getClientIpFromDatabase($em, $clientIp);

        if (is_null($ip)) {
            $this->remeberNewIP($clientIp, $em);
        } else { // ip exists in the database

            if ($ip->getState() == Ip::BLOCKED) {

                $datetime = $ip->getTime();
                $ipTime = $datetime->getTimestamp();

                $timeElapsed = $this->getElapsedTimeAfterBlock($ipTime);

                if ($timeElapsed < $ipBlockTimeSec) { // ip blocked
                    $requestAllowed = false;
                } else { // block time has been finished
                    $ip->setAttempt(0);
                    $ip->setState(Ip::PENDING);
                    $em->persist($ip);
                    $em->flush();
                }
            }
        }

        return $requestAllowed;
    }

    /**
     * @param $session
     */
    public function trackLogoutSysEvent($session)
    {
        $sysEventVal = $session->get(self::SYS_EVENT, NULL);

        if ($sysEventVal != NULL && $sysEventVal == 'LOGOUT') {

            $recentSysUserDataArray = $session->get(self::RECENT_SYS_USER, NULL);

            if ($recentSysUserDataArray != NULL) {

                $sysEvent = new SysEvent();
                $sysEvent->setUserId($recentSysUserDataArray['userId']);
                $sysEvent->setEvent($recentSysUserDataArray['event']);
                $sysEvent->setIp($recentSysUserDataArray['ip']);

                $eventTracker = $this->get('sys_event_tracker');
                $eventTracker->track($sysEvent);

                $session->remove(self::SYS_EVENT);
                $session->remove(self::RECENT_SYS_USER);

            }
        }
    }
}
