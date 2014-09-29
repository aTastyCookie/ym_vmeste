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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Vmeste\SaasBundle\Entity\Campaign;
use Vmeste\SaasBundle\Entity\User;
use Vmeste\SaasBundle\Util\PaginationUtils;

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


        $limit = 2;
        $pageOnSidesLimit = 2;

        $page = $this->getRequest()->query->get("page", 1);

        $em = $this->getDoctrine()->getManager();

        $currentUser = $this->get('security.context')->getToken()->getUser();

        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $currentUser->getId()));

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
            'campaign_url' => "http://" . $this->getRequest()->getHost() . "/payment/",
            'pages' => $pageNumberArray,
            'page' => $page,
            'campaign_created' => $campaignSuccessfullyCreatedMessage);
    }

    /**
     * @Template
     */
    public function showAllAction()
    {

        $campaignSuccessfullyCreatedMessage = null;
        if ($this->getRequest()->query->get("campaign_creation") == 'success')
            $campaignSuccessfullyCreatedMessage = "New campaign has been created successfully!";


        $limit = 2;
        $pageOnSidesLimit = 2;

        $page = $this->getRequest()->query->get("page", 1);

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
            'campaign_url' => "http://" . $this->getRequest()->getHost() . "/payment/",
            'pages' => $pageNumberArray,
            'page' => $page,
            'campaign_created' => $campaignSuccessfullyCreatedMessage);
    }

    /**
     * @Template
     */
    public function createAction(Request $request)
    {

//        $currentUser = $this->get('security.context')->getToken()->getUser();


        $connection = $this->getDoctrine()->getConnection();
        $queryBuilder = $connection->createQueryBuilder();

        $queryBuilder
            ->select('u.id, u.username')
            ->from('user','u')
            ->where('r.role = \'ROLE_USER\'')
            ->join('u','user_role', 'ur', 'u.id = ur.user_id')
            ->join('ur', 'role', 'r', 'r.id = ur.role_id');

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll();

        $userChoices = array();

        foreach($result as $user) {
            $userChoices[$user['id']] = $user['username'];
        }

        $form = $this->createFormBuilder()
            ->add('user', 'choice', array(
                'choices' => $userChoices,
                'label' => 'Пользователи'
            ))
            ->add('title', 'text', array('constraints' => array(
                new NotBlank()
            ), 'label' => 'Название кампании'))
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
            ->add('form_image', 'text', array('constraints' => array(
                new Url()
            ), 'label' => 'Url картинки'))
            // Please enter content of donation form box.
            ->add('form_intro', 'textarea', array('constraints' => array(
                new NotBlank()
            ), 'label' => 'Текст для платежной страницы'))

            // Your donors must be agree with Terms & Conditions before donating
            ->add('form_terms', 'textarea', array('constraints' => array(
                new NotBlank()
            ), 'label' => 'Реквизиты для оферты'))

            // Please enter content of top donors box.
            ->add('top_intro', 'textarea', array('constraints' => array(
                new NotBlank()
            ), 'label' => 'Ненужное поле 2 - Please enter content of top donors box.'))

            // Recent donors box content
            ->add('recent_intro', 'textarea', array('constraints' => array(
                new NotBlank()
            ), 'label' => 'Ненужное поле 3 - Recent donors box content'))

            ->add('save', 'submit', array('label' => 'Создать кампанию'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();

            $em = $this->getDoctrine()->getManager();

            $campaign = new Campaign();
            $campaign->setTitle($data['title']);
            $campaign->setImage($data['form_image']);
            $campaign->setCurrency($data['currency']);
            $campaign->setMinAmount($data['min_amount']);
            $campaign->setFormIntro($data['form_intro']);
            $campaign->setFormTerms($data['form_terms']);
            $campaign->setTopIntro($data['top_intro']);
            $campaign->setRecentIntro($data['recent_intro']);
            $status = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE'));
            $campaign->setStatus($status);

            $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $data['user']));
            $campaign->setUser($user);

            $em->persist($campaign);
            $em->flush();

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

        $id = $this->getRequest()->query->get("id");

        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $id));

        $form = $this->createFormBuilder()
            ->add(
                'title',
                'text',
                array('constraints' => array(
                    new NotBlank()
                ),
                    'label' => 'Название',
                    'data' => $campaign->getTitle()))
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
            ->add(
                'form_image',
                'text',
                array('constraints' => array(
                    new Url()
                ),
                    'label' => 'Url картинки',
                    'data' => $campaign->getImage()))
            // Please enter content of donation form box.
            ->add(
                'form_intro',
                'textarea',
                array('constraints' => array(
                    new NotBlank()
                ),
                    'label' => 'Текст для платежной страницы',
                    'data' => $campaign->getFormIntro()))
            // Your donors must be agree with Terms & Conditions before donating
            ->add(
                'form_terms',
                'textarea',
                array('constraints' => array(
                    new NotBlank()
                ),
                    'label' => 'Реквизиты для оферты',
                    'data' => $campaign->getFormTerms()))

            // Please enter content of top donors box.
            ->add(
                'top_intro',
                'textarea',
                array('constraints' => array(
                    new NotBlank()
                ),
                    'label' => 'Please enter content of top donors box.',
                    'data' => $campaign->getTopIntro()))
            // Recent donors box content
            ->add(
                'recent_intro',
                'textarea',
                array('constraints' => array(
                    new NotBlank()
                ),
                    'label' => 'Recent donors box content',
                    'data' => $campaign->getRecentIntro()))

            ->add('save', 'submit', array('label' => 'Обновить кампанию'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();

            $em = $this->getDoctrine()->getManager();

            $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $id));

            $campaign->setTitle($data['title']);
            $campaign->setImage($data['form_image']);
            $campaign->setCurrency($data['currency']);
            $campaign->setMinAmount($data['min_amount']);
            $campaign->setFormIntro($data['form_intro']);
            $campaign->setFormTerms($data['form_terms']);
            $campaign->setTopIntro($data['top_intro']);
            $campaign->setRecentIntro($data['recent_intro']);
            $status = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ON_MODERATION'));
            $campaign->setStatus($status);

            $em->persist($campaign);
            $em->flush();
        }


        return array(
            'form' => $form->createView(),
        );
    }

    public function blockAction()
    {

        $id = $this->getRequest()->query->get("id");
        $page = $this->getRequest()->query->get("page");

        $em = $this->getDoctrine()->getManager();
        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $id));


        $statusBlocked = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'BLOCKED'));
        $campaign->setStatus($statusBlocked);

        $em->persist($campaign);
        $em->flush();

        return $this->redirect($this->generateUrl('admin_campaign_show_all', array('page' => $page)));
    }

    public function activateAction()
    {

        $id = $this->getRequest()->query->get("id");
        $page = $this->getRequest()->query->get("page");

        $em = $this->getDoctrine()->getManager();
        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $id));


        $statusActivated = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE'));
        $campaign->setStatus($statusActivated);

        $em->persist($campaign);
        $em->flush();

        return $this->redirect($this->generateUrl('admin_campaign_show_all', array('page' => $page)));
    }

    /**
     * @Template
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->query->get("id");
        $page = $this->getRequest()->query->get("page");

        $em = $this->getDoctrine()->getManager();
        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $id));

        $statusActivated = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'DELETED'));
        $campaign->setStatus($statusActivated);

        $em->persist($campaign);
        $em->flush();

        return $this->redirect($this->generateUrl('customer_campaign', array('page' => $page)));
    }


    /**
     * @Template
     */
    public function reportAction()
    {

        $limit = 5;
        $pageOnSidesLimit = 5;

        $page = $this->getRequest()->query->get("page", 1);

        $em = $this->getDoctrine()->getManager();

        $currentUser = $this->get('security.context')->getToken()->getUser();

        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $currentUser->getId()));

        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('t')->from('Vmeste\SaasBundle\Entity\Transaction', 't')
            ->innerJoin('Vmeste\SaasBundle\Entity\Campaign', 'c', 'WITH', 't.campaign = c')
            ->where('c.user = ?1')
            ->setParameter(1, $user);

        $queryBuilder->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        $paginator = new Paginator($queryBuilder, $fetchJoinCollection = false);

        $totalItems = count($paginator);


        $pageCount = (int)ceil($totalItems / $limit);

        $pageNumberArray = PaginationUtils::generatePaginationPageNumbers($page, $pageOnSidesLimit, $pageCount);


        return array(
            'transactions' => $paginator,
            'pages' => $pageNumberArray,
            'page' => $page,
        );
    }

    public function reportExportAction()
    {

        $recurrent = '';

        $em = $this->getDoctrine()->getManager();

        $currentUser = $this->get('security.context')->getToken()->getUser();

        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $currentUser->getId()));

		if ($this->getRequest()->query->get("recurrent", 0) == 1) {
			$recurrent = 'AND r.id is not null';
		} else {
			$recurrent = '';
		}

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
            
        if ($this->getRequest()->query->get("recurrent", 0) == 1) {
        	$queryBuilder
        		->leftJoin('Vmeste\SaasBundle\Entity\Recurrent', 'r', 'WITH', 'r.donor = d and r.campaign_id = c.id')
        		->where('c.user = ?1')
        		->andWhere('r.id is not null');
			$recurrent = '-recurrent'; // FIXME Andrei
		} else {
			$queryBuilder->where('c.user = ?1');
		}
		$queryBuilder->setParameter(1, $user)->orderBy('c.title', 'ASC');

        $report = $queryBuilder->getQuery()->getResult();
//var_dump($report); exit;
		if(!empty($recurrent)) $recurrent = '-recurrent';
        $responseHeaders = array();

        if (strstr($this->getRequest()->server->get('HTTP_USER_AGENT'), "MSIE")) {
            $responseHeaders['pragma'] = 'public';
            $responseHeaders['expires'] = '0';
            $responseHeaders['cache-control'] = 'must-revalidate, post-check=0, pre-check=0';
            $responseHeaders['content-type'] = 'application-download';
            $responseHeaders['content-disposition'] = 'attachment; filename="export-donors' 
            											. $recurrent . '-' . date("Y-m-d") . '.csv"';
            $responseHeaders['content-transfer-encoding'] = 'binary';
        } else {
            $responseHeaders['content-type'] = 'application-download';
            $responseHeaders['content-disposition'] = 'attachment; filename="export-donors' 
            											. $recurrent . '-' . date("Y-m-d") . '.csv"';
        }

        $settings = $user->getSettings();
        $userSettings = $settings[0];
        $separator = $userSettings->getCsvColumnSeparator();

        if ($separator == 'tab') $separator = "\t";

        $output = '"Project"' . $separator . '"FIO"' . $separator . '"E-Mail"' . $separator . '"Recurrent"' . "\r\n";

        foreach ($report as $transaction) {
            $output .= '"' . str_replace('"', '', $transaction->getCampaign()->getTitle()) . '"' . $separator . '"'
                . str_replace('"', '', $transaction->getDonor()->getName()) . '"' . $separator . '"'
                . str_replace('"', "", $transaction->getDonor()->getEmail()) . '"' . $separator . '"';
            if($transaction->getDonor()->getRecurrent() != null)
            	$output .= '1';
            else
            	$output .= '0';
            $output .= '"' ."\r\n";
        }
//var_dump($output); exit;
        $response = new Response($output, 200, $responseHeaders);
        return $response;
        
    }

    /**
     * @Template
     */
    public function paymentPageAction($campaignId)
    {
        $em = $this->getDoctrine()->getManager();
        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => (int)$campaignId));
        $user = $campaign->getUser();
        $settingsCollection = $user->getSettings();
        $userSettings = $settingsCollection[0];
        $yandexKassa = $userSettings->getYandexKassa();

        return array('campaign' => $campaign,
            'yandexKassa' => $yandexKassa,
            'customerNumber' => time(),
            'noIDcustomerNumber' => time(), 'uniqueId' => time());

    }

} 