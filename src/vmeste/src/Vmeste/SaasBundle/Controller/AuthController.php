<?php

namespace Vmeste\SaasBundle\Controller;

use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Vmeste\SaasBundle\Entity\RecoverPassword;
use Vmeste\SaasBundle\Entity\RecoverToken;
use Vmeste\SaasBundle\Entity\Role;
use Vmeste\SaasBundle\Entity\User;
use Vmeste\SaasBundle\Util\Hash;

class AuthController extends Controller
{

    const NON_ACTIVE = 0;

    const ACTIVE = 1;

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

        /**
         *  Insert User
         */
//        $em = $this->getDoctrine()->getManager();
//
//        $admin = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => 1));
//
//        $user = new User();
//        $user->setEmail("zheniq1@gmail.com");
//        $user->setUsername("zheniq1");
//        $user->setPassword("zheniq");
//        $user->setStatus(1);
//        $user->setCreatedBy($admin->getId());
//
//        // TODO Create role ROLE_USER in roles
//        $role = $em->getRepository('Vmeste\SaasBundle\Entity\Role')->findOneBy(array('role' => 'ROLE_USER'));
//        $user->addRole($role);
//        $role->addUser($user);
//
//        $em->persist($user);
//        $em->persist($role);
//        $em->flush();
//
//        echo $role->getName();
//
//        die();


//
//
//        $connection = $this->getDoctrine()->getConnection();
//        $queryBuilder = $connection->createQueryBuilder();

//        $queryBuilder
//            ->select('*')
//            ->from('user','u')
//            ->where('u.email = \'zheniq@gmail.com\'')
//            ->join('u','user_role', 'ur', 'u.id = ur.user_id')
//            ->join('ur', 'role', 'r', 'r.id = ur.role_id');
//
//        $statement = $queryBuilder->execute();
//        $result = $statement->fetchAll();

//        foreach($result as $user) {
//            var_dump($user['role']);
//        }
//
//        die();

//        $dbManager = $this->getDoctrine()->getManager();
//        $dbManager->persist($user);
//        $dbManager->flush();


        $session = $request->getSession();


        // get the login error if there is one
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

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);

//        var_dump($session);
//        die();

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
                        ->setParameter(1, self::NON_ACTIVE)
                        ->setParameter(2, $user->getId())
                        ->setParameter(3, self::ACTIVE)
                        ->getQuery();


                    $logger->info('[RECOVER_EMAIL] Disabled tokens SQL query: ' . $disablePreviousActiveTokensQuery->getSql());

                    $disabledTokenNum = $disablePreviousActiveTokensQuery->execute();

                    $logger->info('[RECOVER_EMAIL] Disabled tokens: ' . $disabledTokenNum . ' for user: ' . $user->getEmail());

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


                    $logger->info('[RECOVER_EMAIL] From:' . $emailFrom . ' To: ' . $user->getEmail() . ' Recover url: ' . $recoverUriWithToken);

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

                } else {
                    $errorMessage = 'User with email ' . $email . ' doesn\'t exists!';
                }


                //

            } else {
                $errorMessage = $errorList[0]->getMessage();
            }


        }


        return array(
            'error' => $errorMessage,
            'success' => $successMessage
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
                    ->findOneBy(array('token' => $token, 'active' => self::ACTIVE));

                if ($recoverToken == null) {
                    array_push($errorMessageArray, "Token doesn't exist!");
                    $hideForm = true;
                } else {
                    $userId = $recoverToken->getUserId();

                    $user = $this->getDoctrine()
                        ->getRepository('Vmeste\SaasBundle\Entity\User')
                        ->findOneBy(array('id' => $userId));

                    if ($user != null) {

                        $user->setPassword(Hash::generatePasswordHash($password));
                        $recoverToken->setActive(0);

                        $em->persist($user);
                        $em->persist($recoverToken);
                        $em->flush();

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

}
