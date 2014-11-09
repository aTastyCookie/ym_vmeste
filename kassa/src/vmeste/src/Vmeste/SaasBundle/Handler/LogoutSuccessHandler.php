<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/16/14
 * Time: 12:22 AM
 */

namespace Vmeste\SaasBundle\Handler;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Vmeste\SaasBundle\Controller\AuthController;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{

    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function onLogoutSuccess(Request $request)
    {
        $referer_url = $request->headers->get('referer');
        $response = new RedirectResponse($referer_url);

        $session = $request->getSession();
        $session->set(AuthController::SYS_EVENT, 'LOGOUT');

        return $response;
    }

}