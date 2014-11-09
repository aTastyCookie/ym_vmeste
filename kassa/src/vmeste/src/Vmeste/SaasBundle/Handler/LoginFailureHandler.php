<?php
/**
 * Created by PhpStorm.
 * User: Eugene
 * Date: 07.11.2014
 * Time: 20:46
 */

namespace Vmeste\SaasBundle\Handler;


use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Vmeste\SaasBundle\Entity\Ip;

class LoginFailureHandler implements EventSubscriberInterface
{

    private $container;

    private $entityManager;

    public function __construct(ContainerInterface $container, EntityManager $entityManager)
    {
        $this->container = $container;
        $this->entityManager = $entityManager;
    }


    public static function getSubscribedEvents()
    {
        return array(
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
        );
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $failureEvent)
    {

        $clientIp = $this->container->get('request')->getClientIp();
        $ipRepository = $this->entityManager->getRepository('Vmeste\SaasBundle\Entity\Ip');

        $ip = $ipRepository->findOneBy(array('ip' => $clientIp));

        $loginAttempt = $ip->getAttempt();

        $loginAttempts = intval($this->container->getParameter('security.login.attempts'));

        if ($loginAttempt < $loginAttempts) {
            $ipRepository->incrementAttempt($clientIp);
            $ip = $ipRepository->findOneBy(array('ip' => $clientIp));
        }

        if ($ip->getAttempt() >= $loginAttempts) {
            $ip->setAttempt(0);
            $ip->setState(Ip::BLOCKED);
            $this->entityManager->flush();

            $router = $this->container->get('router');
            $redirectResponse = new RedirectResponse($router->generate('forgot_pass'));
            return $redirectResponse;
        }

    }
}