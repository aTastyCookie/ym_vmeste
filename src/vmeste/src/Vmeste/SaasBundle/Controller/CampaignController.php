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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Vmeste\SaasBundle\Entity\Campaign;
use Vmeste\SaasBundle\Entity\User;

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
        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('c')->from('Vmeste\SaasBundle\Entity\Campaign', 'c')
            ->leftJoin('Vmeste\SaasBundle\Entity\Status', 's', 'WITH', 'c.status = s.id')
            ->where('s.status <> ?1')
            ->setParameter(1, 'DELETED');

        $queryBuilder->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        $paginator = new Paginator($queryBuilder, $fetchJoinCollection = false);

        $totalItems = count($paginator);

        $pageCount = (int)ceil($totalItems / $limit);

        $pageNumberArray = array();

        if ($page > $pageOnSidesLimit + 1) {
            for ($i = $page - $pageOnSidesLimit; $i < $page; $i++) {
                array_push($pageNumberArray, $i);
            }
        } else {
            for ($i = 1; $i < $page; $i++) {
                array_push($pageNumberArray, $i);
            }
        }

        array_push($pageNumberArray, $page);

        if ($page + $pageOnSidesLimit < $pageCount) {
            for ($i = $page + 1; $i <= $page + $pageOnSidesLimit; $i++) {
                array_push($pageNumberArray, $i);
            }
        } else {
            for ($i = $page + 1; $i <= $pageCount; $i++) {
                array_push($pageNumberArray, $i);
            }
        }

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

        $currentUser = $this->get('security.context')->getToken()->getUser();

        $form = $this->createFormBuilder()
            ->add('title', 'text', array('constraints' => array(
                new NotBlank()
            ), 'label' => ''))
            ->add('min_amount', 'text', array('constraints' => array(
                new NotBlank()
            ), 'label' => ''))
            ->add('currency', 'choice', array(
                'choices' => array(
                    'RUB' => 'RUB',
                ),
                'label' => 'Currency',
                'constraints' => array(
                    new Choice(array(
                            'choices' => array('RUB'),
                            'message' => 'Choose a valid currency.',
                        )
                    )
                )))
            ->add('form_image', 'text', array('constraints' => array(
                new Url()
            ), 'label' => 'Url for image'))
            // Please enter content of donation form box.
            ->add('form_intro', 'textarea', array('constraints' => array(
                new NotBlank()
            ), 'label' => 'Please enter content of donation form box'))

            // Your donors must be agree with Terms & Conditions before donating
            ->add('form_terms', 'textarea', array('constraints' => array(
                new NotBlank()
            ), 'label' => 'Your donors must be agree with Terms & Conditions before donating'))

            // Please enter content of top donors box.
            ->add('top_intro', 'textarea', array('constraints' => array(
                new NotBlank()
            ), 'label' => 'Please enter content of top donors box.'))

            // Recent donors box content
            ->add('recent_intro', 'textarea', array('constraints' => array(
                new NotBlank()
            ), 'label' => 'Recent donors box content'))

            ->add('save', 'submit', array('label' => 'Create Campaign'))
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
            $status = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ON_MODERATION'));
            $campaign->setStatus($status);

            $campaign->setUser($currentUser);

            $em->persist($campaign);
            $em->flush();

            return $this->redirect($this->generateUrl('customer_campaign', array('campaign_creation' => 'success')));
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
                    'label' => '',
                    'data' => $campaign->getTitle()))
            ->add(
                'min_amount',
                'text',
                array('constraints' => array(
                    new NotBlank()
                ),
                    'label' => '',
                    'data' => $campaign->getMinAmount()))
            ->add(
                'currency', 'choice', array(
                'choices' => array(
                    'RUB' => 'RUB',
                ),
                'label' => 'Currency',
                'constraints' => array(
                    new Choice(array(
                            'choices' => array('RUB'),
                            'message' => 'Choose a valid currency.',
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
                'label' => 'Url for image',
                'data' => $campaign->getImage()))
            // Please enter content of donation form box.
            ->add(
                'form_intro',
                'textarea',
                array('constraints' => array(
                    new NotBlank()
                ),
                'label' => 'Please enter content of donation form box',
                'data' => $campaign->getFormIntro()))
            // Your donors must be agree with Terms & Conditions before donating
            ->add(
                'form_terms',
                'textarea',
                array('constraints' => array(
                    new NotBlank()
                ),
                'label' => 'Your donors must be agree with Terms & Conditions before donating',
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

            ->add('save', 'submit', array('label' => 'Update Campaign'))
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

        return $this->redirect($this->generateUrl('customer_campaign', array('page' => $page)));
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

        return $this->redirect($this->generateUrl('customer_campaign', array('page' => $page)));
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
    public function paymentPageAction($campaignId)
    {

        $em = $this->getDoctrine()->getManager();
        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $campaignId));

        return array('campaign' => $campaign, 'customerNumber' => time(), 'noIDcustomerNumber' => time());
    }

} 