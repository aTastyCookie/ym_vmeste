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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Url;
use Vmeste\SaasBundle\Entity\Campaign;
use Vmeste\SaasBundle\Entity\User;
use Vmeste\SaasBundle\Entity\YandexKassa;
use Vmeste\SaasBundle\Entity\SysEvent;
use Vmeste\SaasBundle\Util\PaginationUtils;
use Vmeste\SaasBundle\Util\Clear;
use Vmeste\SaasBundle\Validator\ForbiddenUriConstraint;
use Vmeste\SaasBundle\Validator\ForbiddenUriValidator;

class CampaignController extends Controller
{

    /**
     * @Template
     */
    public function homeAction()
    {

        $campaignSuccessfullyCreatedMessage = null;
        if ($this->getRequest()->query->get("campaign_creation") == 'success')
            $campaignSuccessfullyCreatedMessage = "New campaign has been created successfully!";

        $limit = $this->container->getParameter('paginator.page.items');
        $pageOnSidesLimit = 10;

        $page = Clear::integer($this->getRequest()->query->get("page", 1), 1);

        $em = $this->getDoctrine()->getManager();

        $currentUser = $this->get('security.context')->getToken()->getUser();

        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $currentUser->getId()));

        $settingsCollection = $user->getSettings();
        $userSettings = $settingsCollection[0];
        $yandexKassa = $userSettings->getYandexKassa();

        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('c')->from('Vmeste\SaasBundle\Entity\Campaign', 'c')
            ->leftJoin('Vmeste\SaasBundle\Entity\Status', 's', 'WITH', 'c.status = s.id')
            ->where('s.status <> ?1')
            ->andWhere('c.user = ?2')
            ->setParameter(1, 'DELETED')
            ->setParameter(2, $user);

        $queryBuilder->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        $paginator = new Paginator($queryBuilder, $fetchJoinCollection = false);

        $totalItems = count($paginator);

        $pageCount = (int)ceil($totalItems / $limit);

        $pageNumberArray = PaginationUtils::generatePaginationPageNumbers($page, $pageOnSidesLimit, $pageCount);

        return array(
            'campaigns' => $paginator,
            'campaign_url' => $this->container->getParameter('recurrent.apphost'),
            'pages' => $pageNumberArray,
            'page' => $page,
            'campaign_created' => $campaignSuccessfullyCreatedMessage,
            'yk' => $yandexKassa);
    }

    /**
     * @Template
     */
    public function showAllAction()
    {

        $campaignSuccessfullyCreatedMessage = null;
        if ($this->getRequest()->query->get("campaign_creation") == 'success')
            $campaignSuccessfullyCreatedMessage = "New campaign has been created successfully!";


        $limit = $this->container->getParameter('paginator.page.items');
        $pageOnSidesLimit = 10;

        $page = Clear::integer($this->getRequest()->query->get("page", 1), 1);

        $em = $this->getDoctrine()->getManager();

        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('c')->from('Vmeste\SaasBundle\Entity\Campaign', 'c')
            ->leftJoin('Vmeste\SaasBundle\Entity\Status', 's', 'WITH', 'c.status = s.id');

        $queryBuilder->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        $paginator = new Paginator($queryBuilder, $fetchJoinCollection = false);

        $totalItems = count($paginator);

        $pageCount = (int)ceil($totalItems / $limit);

        $pageNumberArray = PaginationUtils::generatePaginationPageNumbers($page, $pageOnSidesLimit, $pageCount);

        return array(
            'campaigns' => $paginator,
            'campaign_url' => $this->container->getParameter('recurrent.apphost'),
            'pages' => $pageNumberArray,
            'page' => $page,
            'campaign_created' => $campaignSuccessfullyCreatedMessage);
    }

    /**
     * @Template
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
//        $currentUser = $this->get('security.context')->getToken()->getUser();
        $connection = $this->getDoctrine()->getConnection();
        $queryBuilder = $connection->createQueryBuilder();

        $queryBuilder
            ->select('u.id, u.username')
            ->from('user', 'u')
            ->where('r.role = \'ROLE_USER\'')
            ->join('u', 'user_role', 'ur', 'u.id = ur.user_id')
            ->join('ur', 'role', 'r', 'r.id = ur.role_id');

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll();

        $userChoices = array();

        foreach ($result as $user) {
            $userEntity = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $user['id']));
            $settingsCollection = $userEntity->getSettings();
            if(isset($settingsCollection[0])) {
                $userSettings = $settingsCollection[0];
                if($userSettings) {
                    $yandexKassa = $userSettings->getYandexKassa();
                    if($yandexKassa) {
                        $userChoices[$user['id']] = $user['username'];
                    }
                }
            }
        }

        $form = $this->createFormBuilder()
            ->add('user', 'choice', array(
                'choices' => $userChoices,
                'label' => 'Пользователи'
            ))
            ->add('title', 'text', array('constraints' => array(
                new NotBlank()
            ), 'label' => 'Название кампании'))
            ->add('subtitle', 'text',
                array('constraints' => array(
                    new NotBlank()
                ), 'label' => 'Подзаголовок')
            )
            ->add('url', 'text',
                array('constraints' => array(
                    new NotBlank(),
                    new Regex(
                        array(
                            'pattern' => '/^[a-zA-Z0-9]?([a-zA-Z0-9]-?)+[a-zA-Z0-9]$/',
                            'message' => 'Формат введенного URL неверен!'
                        ),
                        new ForbiddenUriConstraint()
                    ),
                ), 'label' => 'URL (только латинские буквы, цифры и дефис)')
            )
            ->add(
                'bigPic', 'file', array(
                    'label' => 'Изображение (рекомендации: 710х500px, горизонтальная ориентация)',
                    'constraints' => array(
                        new Image(array(
                                'maxSize' => '5M',
                            )
                        )
                    )
                )
            )
            ->add('min_amount', 'text', array('constraints' => array(
                new NotBlank()
            ), 'label' => 'Минимальный взнос'))
            ->add('currency', 'choice', array(
                'choices' => array(
                    'RUB' => 'RUB',
                ),
                'label' => 'Валюта',
                'constraints' => array(
                    new Choice(array(
                            'choices' => array('RUB'),
                            'message' => 'Выберите корректную валюту',
                        )
                    )
                )))
            /* ->add('form_image', 'text', array('constraints' => array(
                 new Url()
             ), 'label' => 'Url картинки'))*/
            // Please enter content of donation form box.
            ->add('form_intro', 'textarea', array('constraints' => array(
                new NotBlank()
            ), 'label' => 'Текст для платежной страницы',
                'attr' => array('class' => 'campaign_text')))
            ->add('save', 'submit', array('label' => 'Создать кампанию'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();

            $em = $this->getDoctrine()->getManager();

            $campaign = new Campaign();
            $campaign->setUploadDir($this->container->getParameter('image.upload.dir'));
            $campaign->setTitle(Clear::removeCRLF($data['title'], true, false));
            $campaign->setSubTitle($data['subtitle']);
            $campaign->setCurrency($data['currency']);
            $campaign->setMinAmount(Clear::number($data['min_amount']));
            $campaign->setFormIntro($data['form_intro']);
            $campaign->setUrl($data['url']);
            $status = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE'));
            $campaign->setStatus($status);
            $campaign->setBigPic($data['bigPic']);
            $campaign->upload();

            $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $data['user']));
            $campaign->setUser($user);

            $em->persist($campaign);
            $em->flush();

            $user = $this->get('security.context')->getToken()->getUser();
            $sysEvent = new SysEvent();
            $sysEvent->setUserId($user->getId());
            $sysEvent->setEvent(SysEvent::CREATE_CAMPAIGN);
            $sysEvent->setIp($this->container->get('request')->getClientIp());
            $eventTracker = $this->get('sys_event_tracker');
            $eventTracker->track($sysEvent);

            return $this->redirect($this->generateUrl('admin_campaign_show_all', array('campaign_creation' => 'success')));
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

        if ($id < 1) {
            throw $this->createNotFoundException();
        }

        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $id));

        if (!$campaign) {
            throw $this->createNotFoundException();
        }

        $form = $this->createFormBuilder()
            ->add(
                'title',
                'text',
                array('constraints' => array(
                    new NotBlank()
                ),
                    'label' => 'Название',
                    'data' => $campaign->getTitle()))
            ->add('subtitle', 'text',
                array('constraints' => array(
                    new NotBlank()
                ),
                    'label' => 'Подзаголовок (на что собираем)',
                    'data' => $campaign->getSubTitle())
            )
            ->add('url', 'text',
                array('constraints' => array(
                    new NotBlank(),
                    new Regex(
                        array(
                            'pattern' => '/^[a-zA-Z0-9]?([a-zA-Z0-9]-?)+[a-zA-Z0-9]$/',
                            'message' => 'Формат введенного URL неверен!'
                        )
                    ),
                    new ForbiddenUriConstraint(array('previousUri' => $campaign->getUrl()))
                ),
                    'label' => 'URL (только латинские буквы, цифры и дефис)',
                    'data' => $campaign->getUrl())
            )
            ->add('bigPic', 'file', array(
                    'label' => 'Изображение (рекомендации: 710х500px, горизонтальная ориентация)',
                    'constraints' => array(
                        new Image(array(
                                'maxSize' => '5M',
                            )
                        )
                    ),
                    'required' => false
                )
            )
            ->add(
                'min_amount',
                'text',
                array('constraints' => array(
                    new NotBlank()
                ),
                    'label' => 'Минимальный взнос',
                    'data' => $campaign->getMinAmount()))
            ->add(
                'currency', 'choice', array(
                    'choices' => array(
                        'RUB' => 'RUB',
                    ),
                    'label' => 'Валюта',
                    'constraints' => array(
                        new Choice(array(
                                'choices' => array('RUB'),
                                'message' => 'Выберите корректную валюту',
                            )
                        )
                    ),
                    'data' => $campaign->getCurrency()))
            // Please enter content of donation form box.
            ->add(
                'form_intro',
                'textarea',
                array('constraints' => array(
                    new NotBlank()
                ),
                    'label' => 'Текст для платежной страницы',
                    'data' => $campaign->getFormIntro(),
                    'attr' => array('class' => 'campaign_text')))
            ->add('save', 'submit', array('label' => 'Обновить кампанию'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $id));

            $data = $form->getData();

            $campaign->setUploadDir($this->container->getParameter('image.upload.dir'));
            $campaign->setTitle(Clear::removeCRLF($data['title'], true, false));
            $campaign->setSubTitle($data['subtitle']);
            $campaign->setUrl($data['url']);
            $campaign->setCurrency($data['currency']);
            $campaign->setMinAmount(Clear::number($data['min_amount']));
            $campaign->setFormIntro($data['form_intro']);

            if ($data['bigPic'] != null)
                $campaign->setBigPic($data['bigPic']);

            $campaign->upload();

            $status = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE'));
            $campaign->setStatus($status);

            $em->persist($campaign);
            $em->flush();

            $user = $this->get('security.context')->getToken()->getUser();
            $sysEvent = new SysEvent();
            $sysEvent->setUserId($user->getId());
            $sysEvent->setEvent(SysEvent::UPDATE_CAMPAIGN . ' ' . $id);
            $sysEvent->setIp($this->container->get('request')->getClientIp());
            $eventTracker = $this->get('sys_event_tracker');
            $eventTracker->track($sysEvent);
        }


        return array(
            'form' => $form->createView(),
            'bigImage' => ($campaign->getBigPicPath() != null) ? $this->container->getParameter('image.upload.dir') . $campaign->getBigPicPath() : null,
        );
    }

    public function blockAction()
    {

        $id = Clear::integer($this->getRequest()->query->get("id"));

        if ($id < 1) {
            throw $this->createNotFoundException();
        }

        $page = Clear::integer($this->getRequest()->query->get("page"));

        $em = $this->getDoctrine()->getManager();
        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $id));

        if (!$campaign) {
            throw $this->createNotFoundException();
        }

        $statusBlocked = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'BLOCKED'));
        $campaign->setStatus($statusBlocked);

        $em->persist($campaign);
        $em->flush();

        $user = $this->get('security.context')->getToken()->getUser();
        $sysEvent = new SysEvent();
        $sysEvent->setUserId($user->getId());
        $sysEvent->setEvent(SysEvent::BLOCK_CAMPAIGN);
        $sysEvent->setIp($this->container->get('request')->getClientIp());
        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);

        $redirectUrl = $this->generateUrl('login');

        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $redirectUrl = $this->generateUrl('admin_campaign_show_all', array('page' => $page));
            } else if ($this->get('security.context')->isGranted('ROLE_USER')) {
                $redirectUrl = $this->generateUrl('customer_campaign', array('page' => $page));
            }
        }

        return $this->redirect($redirectUrl);
    }

    public function activateAction()
    {

        $id = Clear::integer($this->getRequest()->query->get("id"));

        if ($id < 1) {
            throw $this->createNotFoundException();
        }

        $page = Clear::integer($this->getRequest()->query->get("page"));

        $em = $this->getDoctrine()->getManager();
        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $id));

        if (!$campaign) {
            throw $this->createNotFoundException();
        }


        $statusActivated = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE'));
        $campaign->setStatus($statusActivated);

        $em->persist($campaign);
        $em->flush();

        $user = $this->get('security.context')->getToken()->getUser();
        $sysEvent = new SysEvent();
        $sysEvent->setUserId($user->getId());
        $sysEvent->setEvent(SysEvent::ACTIVATE_CAMPAIGN);
        $sysEvent->setIp($this->container->get('request')->getClientIp());
        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);

        return $this->redirect($this->generateUrl('admin_campaign_show_all', array('page' => $page)));
    }

    /**
     * @Template
     */
    public function deleteAction()
    {
        $id = Clear::integer($this->getRequest()->query->get("id"));

        if ($id < 1) {
            throw $this->createNotFoundException();
        }

        $page = Clear::integer($this->getRequest()->query->get("page"));

        $em = $this->getDoctrine()->getManager();
        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $id));

        if(!$campaign) {
            throw $this->createNotFoundException();
        }

        $statusActivated = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'DELETED'));
        $campaign->setStatus($statusActivated);

        $em->persist($campaign);
        $em->flush();

        $user = $this->get('security.context')->getToken()->getUser();
        $sysEvent = new SysEvent();
        $sysEvent->setUserId($user->getId());
        $sysEvent->setEvent(SysEvent::DELETE_CAMPAIGN);
        $sysEvent->setIp($this->container->get('request')->getClientIp());
        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);

        $redirectUrl = $this->generateUrl('login');

        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $redirectUrl = $this->generateUrl('admin_campaign_show_all', array('page' => $page));
            } else if ($this->get('security.context')->isGranted('ROLE_USER')) {
                $redirectUrl = $this->generateUrl('customer_campaign', array('page' => $page));
            }
        }

        return $this->redirect($redirectUrl);
    }


    /**
     * @Template
     */
    public function reportAction()
    {

        $limit = $this->container->getParameter('paginator.page.items');
        $pageOnSidesLimit = 10;

        $page = Clear::integer($this->getRequest()->query->get("page", 1), 1);

        $campaignId = Clear::integer($this->getRequest()->query->get("campaignId", null), null);


        $em = $this->getDoctrine()->getManager();

        $currentUser = $this->get('security.context')->getToken()->getUser();

        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $currentUser->getId()));

        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('t')->from('Vmeste\SaasBundle\Entity\Transaction', 't')
            ->innerJoin('Vmeste\SaasBundle\Entity\Campaign', 'c', 'WITH', 't.campaign = c')
            ->where('c.user = ?1');

        if (!is_null($campaignId)) {
            $queryBuilder->andWhere('c.id = ?2')
                ->setParameter(2, $campaignId);
        }

        $queryBuilder->setParameter(1, $user);

        $queryBuilder->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        $paginator = new Paginator($queryBuilder, $fetchJoinCollection = false);

        $totalItems = count($paginator);


        $pageCount = (int)ceil($totalItems / $limit);

        $pageNumberArray = PaginationUtils::generatePaginationPageNumbers($page, $pageOnSidesLimit, $pageCount);


        return array(
            'transactions' => $paginator,
            'pages' => $pageNumberArray,
            'page' => $page,
            'campaignId' => $campaignId
        );
    }

    public function reportExportAction()
    {


        $em = $this->getDoctrine()->getManager();

        $currentUser = $this->get('security.context')->getToken()->getUser();

        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $currentUser->getId()));

        if (Clear::integer($this->getRequest()->query->get("recurrent", 0), 0) == 1) {
            $recurrent = 'AND r.id is not null';
        } else {
            $recurrent = '';
        }

        $campaignId = Clear::integer($this->getRequest()->query->get("campaignId", null), null);

        /*$query = $em->createQuery("SELECT c.title, d.name, d.email, r.id as rid
                                    FROM Vmeste\SaasBundle\Entity\Campaign c
                                    INNER JOIN Vmeste\SaasBundle\Entity\Donor d WITH (d.campaign_id = c.id)
                                    INNER JOIN Vmeste\SaasBundle\Entity\Transaction t WITH (t.donor_id = d.id)
                                    LEFT JOIN Vmeste\SaasBundle\Entity\Recurrent r WITH
                                    (r.donator_id = d.id and r.campaign_id = c.id)
                                    WHERE c.user = :user $recurrent
                                    ORDER BY c.title ASC")
                                ->setParameter('user', $user);

        $report = $query->getResult();
        var_dump($report); exit;*/

        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('t')->from('Vmeste\SaasBundle\Entity\Transaction', 't')
            ->innerJoin('Vmeste\SaasBundle\Entity\Campaign', 'c', 'WITH', 't.campaign = c')
            ->innerJoin('Vmeste\SaasBundle\Entity\Donor', 'd', 'WITH', 't.donor = d');


        if (Clear::integer($this->getRequest()->query->get("recurrent", 0), 0) == 1) {
            $queryBuilder
                ->leftJoin('Vmeste\SaasBundle\Entity\Recurrent', 'r', 'WITH', 'r.donor = d and r.campaign = c')
                ->where('c.user = ?1')
                ->andWhere('r.id is not null');
            $recurrent = '-recurrent';
        } else {
            $queryBuilder->where('c.user = ?1');
        }


        if (!is_null($campaignId)) {
            $queryBuilder->andWhere('c.id = ?2')
                ->setParameter(2, $campaignId);
        }

        $queryBuilder->setParameter(1, $user)->orderBy('c.title', 'ASC');

        $report = $queryBuilder->getQuery()->getResult();

        if (!empty($recurrent)) $recurrent = '-recurrent';
        $responseHeaders = array();

        if (strstr($this->getRequest()->server->get('HTTP_USER_AGENT'), "MSIE")) {
            $responseHeaders['pragma'] = 'public';
            $responseHeaders['expires'] = '0';
            $responseHeaders['cache-control'] = 'must-revalidate, post-check=0, pre-check=0';
            $responseHeaders['content-type'] = 'text/csv; charset=utf-8';
            $responseHeaders['content-disposition'] = 'attachment; filename="export-donors'
                . $recurrent . '-' . date("Y-m-d") . '.csv"';
            $responseHeaders['content-transfer-encoding'] = 'binary';
        } else {
            $responseHeaders['content-type'] = 'text/csv; charset=utf-8';
            $responseHeaders['content-disposition'] = 'attachment; filename="export-donors'
                . $recurrent . '-' . date("Y-m-d") . '.csv"';
        }

        $settings = $user->getSettings();
        $userSettings = $settings[0];
        $separator = $userSettings->getCsvColumnSeparator();

        if ($separator == 'tab') $separator = "\t";

        $output = chr(0xEF) . chr(0xBB) . chr(0xBF) .
            '"Проект"' . $separator
            . '"ФИО"' . $separator
            . '"Email"' . $separator
            . '"Признак подписчика"' . $separator
            . '"Инитный"' . $separator . "\r\n";

        foreach ($report as $transaction) {
            $output .= '"' . str_replace('"', '', $transaction->getCampaign()->getTitle()) . '"' . $separator . '"'
                . str_replace('"', '', $transaction->getDonor()->getName()) . '"' . $separator . '"'
                . str_replace('"', "", $transaction->getDonor()->getEmail()) . '"' . $separator . '"';

            $transaction->getDonor()->getRecurrent() != null ? $output .= '1' : $output .= '0';
            $initial = $transaction->getInitial();
            $output .= '"' . $separator . '"';
            switch($initial) {
                case 1: $output .= 'инитный'; break;
                case 2: $output .= 'повтор'; break;
                default: $output .= ''; break;
            }
            $output .= "\r\n";
        }

        $response = new Response($output, 200, $responseHeaders);
        return $response;

    }

    /**
     * @Template
     */
    public function paymentPageAction($campaignUrl)
    {
        $em = $this->getDoctrine()->getManager();

        $status = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE'));

        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')
            ->findOneBy(array('url' => Clear::string_without_quotes($campaignUrl), 'status'=>$status));

        if (!$campaign) {
            echo "Запрошенная кампания удалена либо не существует";
            exit;
            //throw $this->createNotFoundException();
        }

        $user = $campaign->getUser();
        $userLogoPath = $user->getLogoPath();

        $continue = false;
        $settingsCollection = $user->getSettings();
        if(isset($settingsCollection[0])) {
            $userSettings = $settingsCollection[0];
            if($userSettings) {
                $yandexKassa = $userSettings->getYandexKassa();
                if($yandexKassa) {
                    $continue = true;
                }
            }
        }
        if(!$continue) {
            $sysEvent = new SysEvent();
            $sysEvent->setUserId($user->getId());
            $sysEvent->setEvent("GET CAMPAIGN '$campaignUrl' ERROR: Не установлены настройки Яндекс.Кассы");
            $sysEvent->setIp($this->container->get('request')->getClientIp());
            $eventTracker = $this->get('sys_event_tracker');
            $eventTracker->track($sysEvent);
            echo "Произошла ошибка. Обратитесь к администратору";
            exit;
        }

        $sandboxMode = $yandexKassa->getSandbox();

        $paymentHost = $this->container->getParameter('production.payment.host');

        if ($sandboxMode == YandexKassa::SANDBOX_ENABLED)
            $paymentHost = $this->container->getParameter('sandbox.payment.host');

        $paymentPage = $this->container->getParameter('recurrent.apphost') . $campaign->getUrl();

        $imageStoragePath = $this->container->getParameter('image.upload.dir');

        return array('campaign' => $campaign,
            'description' => nl2br($campaign->getFormIntro()),
            'yandexKassa' => $yandexKassa,
            'customerNumber' => time(),
            'noIDcustomerNumber' => time(),
            'uniqueId' => time(),
            'paymentPage' => $paymentPage,
            'paymentHost' => $paymentHost,
            'imageStoragePath' => $imageStoragePath,
            'settings' => $userSettings,
            'logo' => $userLogoPath,
        );

    }

    /**
     * @Template
     */
    public function ofertaAction($campaignUrl)
    {
        $em = $this->getDoctrine()->getManager();
        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')
            ->findOneBy(array('url' => Clear::string_without_quotes($campaignUrl)));

        if (!$campaign) {
            throw $this->createNotFoundException();
        }

        $user = $campaign->getUser();
        $settingsCollection = $user->getSettings();
        $userSettings = $settingsCollection[0];
        $months = array(1 => 'января',
            2 => 'февраля',
            3 => 'марта',
            4 => 'апреля',
            5 => 'мая',
            6 => 'июня',
            7 => 'июля',
            8 => 'августа',
            9 => 'сентября',
            10 => 'октября',
            11 => 'ноября',
            12 => 'декабря');
        $time = "«" . date("d") . "» " . $months[date('n')] . " " . date("Y") . "г.";
        return array('settings' => $userSettings, 'time' => $time);
    }
} 