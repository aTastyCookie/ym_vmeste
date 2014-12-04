<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/18/14
 * Time: 9:42 PM
 */

namespace Vmeste\SaasBundle\Controller;


use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vmeste\SaasBundle\Entity\Settings;
use Vmeste\SaasBundle\Entity\User;
use Vmeste\SaasBundle\Entity\YandexKassa;
use Vmeste\SaasBundle\Entity\SysEvent;
use Vmeste\SaasBundle\Util\Hash;
use Vmeste\SaasBundle\Util\Clear;
use Vmeste\SaasBundle\Util\PaginationUtils;
use Symfony\Component\Form\Form;

class UserController extends Controller
{

    /**
     * @Template
     */
    public function homeAction()
    {

        $userSuccessfullyCreatedMessage = null;
        if($this->getRequest()->query->get("user_creation") == 'success')
            $userSuccessfullyCreatedMessage = "Пользователь успешно создан!";

        $limit = $this->container->getParameter('paginator.page.items');
        $pageOnSidesLimit = 10;

        $page = Clear::integer($this->getRequest()->query->get("page", 1), 1);

        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('u')
                        ->from('Vmeste\SaasBundle\Entity\User', 'u')
                        ->leftJoin('u.settings', 's');

        $queryBuilder->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        $paginator = new Paginator($queryBuilder, $fetchJoinCollection = true);

        $totalItems = count($paginator);

        $pageCount = (int) ceil($totalItems / $limit);

        $pageNumberArray = PaginationUtils::generatePaginationPageNumbers($page, $pageOnSidesLimit, $pageCount);

        return array('users' => $paginator, 'pages' => $pageNumberArray, 'page' => $page, 'user_created' => $userSuccessfullyCreatedMessage);
    }

    /**
     * @Template
     */
    public function createAction(Request $request)
    {

        $currentUser = $this->get('security.context')->getToken()->getUser();

        $form = $this->createFormBuilder()
            ->add('email', 'text', array('constraints' => array(
                new NotBlank(),
                new Email(),
            )))
            ->add('username', 'text', array('constraints' => array(
                new NotBlank(),
                new Length(array('min' => 3, 'max' => 25)),
            )))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Пароли должны совпадать.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options' => array('label' => 'Пароль'),
                'second_options' => array('label' => 'Повторите пароль'),
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 6, 'max' => 64)),
                )))
            ->add('role', 'choice', array(
                'choices' => array(
                    'ROLE_USER' => 'Пользователь',
                    'ROLE_ADMIN' => 'Администратор',
                ),
                'label' => 'Role',
                'constraints' => array(
                    new Choice(array(
                            'choices' => array('ROLE_USER', 'ROLE_ADMIN'),
                            'message' => 'Выберите корректную роль.',
                        )
                )
            )))
            ->add('logo', 'file', array(
                    'label' => 'Логотип (рекомендации: 100х70px, горизонтальная ориентация, прозрачный фон (PNG-24))',
                    'constraints' => array(
                        new Image(
                            array(
                                'maxSize' => '500k',
                            )
                        )
                    ),
                    'required' => false
                )
            )
            ->add('save', 'submit', array('label' => 'Создать пользователя'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();

            $em = $this->getDoctrine()->getManager();

            if($this->checkEmail($em, $data['email'])) {
                $user = new User();

                $user->setUploadDir($this->container->getParameter('image.upload.dir'));

                $user->setUsername($data['username']);
                $user->setEmail($data['email']);

                if(!empty($data['logo'])) {
                    $user->setLogo($data['logo']);
                    $user->upload();
                }

                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($data['password'], $user->getSalt());
                $user->setPassword($password);

                $role = $em->getRepository('Vmeste\SaasBundle\Entity\Role')->findOneBy(array('role' => $data['role']));
                $user->addRole($role);

                $userEvent = $this->get('security.context')->getToken()->getUser();
                $sysEvent = new SysEvent();
                $sysEvent->setUserId($userEvent->getId());

                if($data['role'] == 'ROLE_USER') {
                    $sysEvent->setEvent(SysEvent::CREATE_USER);
                } else {
                    $sysEvent->setEvent(SysEvent::CREATE_ADMIN);
                }

                $role->addUser($user);
                $status = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE'));
                $user->addStatus($status);
                $status->addUser($user);
                $user->setCreatedBy($currentUser->getId());

                $em->persist($user);
                $em->flush();

                $sysEvent->setIp($this->container->get('request')->getClientIp());
                $eventTracker = $this->get('sys_event_tracker');
                $eventTracker->track($sysEvent);

                return $this->redirect($this->generateUrl('admin_user', array('user_creation' => 'success')));
            } else {
                echo gettype($form->get('email'));
                exit;
                    //->addError(new FormError('Такой email уже занят'));
            }
        }
        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Template
     */
    public function editAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $id = Clear::integer($this->getRequest()->query->get("id"));

        if($id<1) {
            throw $this->createNotFoundException();
        }

        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $id));

        if(!$user) {
            throw $this->createNotFoundException();
        }



        $builder = $this->createFormBuilder()
            ->add('email', 'text', array('constraints' => array(
                new NotBlank(),
                new Email(),
            ), 'data' => $user->getEmail()))
            ->add('username', 'text', array('constraints' => array(
                new NotBlank(),
                new Length(array('min' => 3, 'max' => 25)),
            ),  'data' => $user->getUsername()))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Пароли должны совпадать.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => false,
                'first_options' => array('label' => 'Пароль'),
                'second_options' => array('label' => 'Повторите пароль'),
                'constraints' => array(
                    new Length(array('min' => 6, 'max' => 64)),
                )));
        if($user->getRole() != 'ROLE_ADMIN') {
            $builder->add('role', 'choice', array(
                'choices' => array(
                    'ROLE_USER' => 'Пользователь',
                    'ROLE_ADMIN' => 'Администратор',
                ),
                'data' => $user->getRole(),
                'label' => 'Роль',
                'constraints' => array(
                    new Choice(array(
                            'choices' => array('ROLE_USER', 'ROLE_ADMIN'),
                            'message' => 'Выберите корректную роль.',
                        )
                    )
                )));
        }

        $form = $builder->add('logo', 'file', array(
                    'label' => 'Логотип (рекомендации: 100х70px, горизонтальная ориентация, прозрачный фон (PNG-24))',
                    'constraints' => array(
                        new Image(
                            array(
                                'maxSize' => '500k',
                            )
                        )
                    ),
                    'required' => false
                )
            )
            ->add('save', 'submit', array('label' => 'Обновить пользователя'))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {

            $data = $form->getData();

            $user->setUploadDir($this->container->getParameter('image.upload.dir'));

            $user->setUsername($data['username']);
            $user->setEmail($data['email']);

            if(!empty($data['logo'])) {
                $user->setLogo($data['logo']);
                $user->upload();
            }

            if($data['password'] != NULL) {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($data['password'], $user->getSalt());
                $user->setPassword($password);
            }

            if($data['role'] != $user->getRole()) {

                $currentRole = $em->getRepository('Vmeste\SaasBundle\Entity\Role')->findOneBy(array('role' => $user->getRole()));
                $user->removeRole($currentRole);
                $currentRole->removeUser($user);

                $newRole = $em->getRepository('Vmeste\SaasBundle\Entity\Role')->findOneBy(array('role' => $data['role']));
                $user->addRole($newRole);
                $newRole->addUser($user);
            }

            $em->persist($user);
            $em->flush();

            $userEvent = $this->get('security.context')->getToken()->getUser();
            $sysEvent = new SysEvent();
            $sysEvent->setUserId($userEvent->getId());

            if($data['role'] == 'ROLE_USER') {
                $sysEvent->setEvent(SysEvent::UPDATE_ADMIN);
            } else {
                $sysEvent->setEvent(SysEvent::UPDATE_USER);
            }

            $sysEvent->setIp($this->container->get('request')->getClientIp());
            $eventTracker = $this->get('sys_event_tracker');
            $eventTracker->track($sysEvent);

            return $this->redirect($this->generateUrl('admin_user', array('user_creation' => 'success')));
        }


        //return $this->redirect($this->generateUrl('admin_user', array('user_modifying' => 'success')));

        return array(
            'form' => $form->createView(),
            'logo' => ($user->getLogoPath() != null) ? $this->container->getParameter('image.upload.dir') . $user->getLogoPath() : null,
        );

    }

    public function blockAction()
    {
        $id = Clear::integer($this->getRequest()->query->get("id"));

        if($id<1) {
            throw $this->createNotFoundException();
        }

        $page = Clear::integer($this->getRequest()->query->get("page"));

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $id));

        if(!$user) {
            throw $this->createNotFoundException();
        }

        $statuses = $user->getStatuses();
        $currentStatus = $statuses[0];

        $user->removeStatus($currentStatus);

        $statusBlocked = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'BLOCKED'));
        $user->addStatus($statusBlocked);

        $em->persist($user);
        $em->flush();

        $userEvent = $this->get('security.context')->getToken()->getUser();
        $sysEvent = new SysEvent();
        $sysEvent->setUserId($userEvent->getId());
        $sysEvent->setEvent(SysEvent::BLOCK_USER);
        $sysEvent->setIp($this->container->get('request')->getClientIp());
        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);

        return $this->redirect($this->generateUrl('admin_user', array('page' => $page)));
    }

    public function activateAction()
    {
        $id = Clear::integer($this->getRequest()->query->get("id"));

        if($id<1) {
            throw $this->createNotFoundException();
        }

        $page = Clear::integer($this->getRequest()->query->get("page"));

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $id));

        if(!$user) {
            throw $this->createNotFoundException();
        }

        $statuses = $user->getStatuses();
        $currentStatus = $statuses[0];

        $user->removeStatus($currentStatus);

        $statusActivated = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE'));
        $user->addStatus($statusActivated);

        $em->persist($user);
        $em->flush();

        $userEvent = $this->get('security.context')->getToken()->getUser();
        $sysEvent = new SysEvent();
        $sysEvent->setUserId($userEvent->getId());
        $sysEvent->setEvent(SysEvent::ACTIVATE_USER);
        $sysEvent->setIp($this->container->get('request')->getClientIp());
        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);

        return $this->redirect($this->generateUrl('admin_user', array('page' => $page)));
    }

    /**
     * @Template
     */
    public function deleteAction()
    {
        $id = Clear::integer($this->getRequest()->query->get("id"));

        if($id<1) {
            throw $this->createNotFoundException();
        }

        $page = Clear::integer($this->getRequest()->query->get("page"));

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $id));

        if(!$user) {
            throw $this->createNotFoundException();
        }

        $settingsCollection = $user->getSettings();

        if(isset($settingsCollection[0])) {
            $userSettings = $settingsCollection[0];
            if($userSettings) {
                $yandexKassa = $userSettings->getYandexKassa();
                if($yandexKassa){
                    $campaigns = $user->getCampaigns();
                    foreach($campaigns as $campaign) {
                        $donors = $em->getRepository('Vmeste\SaasBundle\Entity\Donor')->findBy(array('campaign_id' => $campaign->getId()));
                        foreach($donors as $donor) {
                            $transactions = $em->getRepository('Vmeste\SaasBundle\Entity\Transaction')->findBy(array('donor' => $donor));
                            foreach($transactions as $transaction) {
                                $em->remove($transaction);
                            }
                            $recurrents = $em->getRepository('Vmeste\SaasBundle\Entity\Recurrent')->findBy(array('donor' => $donor));
                            foreach($recurrents as $recurrent) {
                                $em->remove($recurrent);
                            }
                            $em->remove($donor);
                        }
                        $em->remove($campaign);
                    }

                    $em->remove($userSettings);
                    $em->remove($yandexKassa);
                }
            }
        }

        $em->remove($user);

        $em->flush();

        $userEvent = $this->get('security.context')->getToken()->getUser();
        $sysEvent = new SysEvent();
        $sysEvent->setUserId($userEvent->getId());
        $sysEvent->setEvent('DELETE USER '.$id);
        $sysEvent->setIp($this->container->get('request')->getClientIp());
        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);
        return $this->redirect($this->generateUrl('admin_user', array('page' => $page)));
    }

    public function checkEmail($em, $email) {
        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('email' => $email));
        if(gettype($user) == 'object') return false;
        return true;

    }
} 