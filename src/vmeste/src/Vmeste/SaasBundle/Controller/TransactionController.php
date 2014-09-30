<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/25/14
 * Time: 11:54 PM
 */

namespace Vmeste\SaasBundle\Controller;


use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vmeste\SaasBundle\Entity\Donor;
use Vmeste\SaasBundle\Entity\Transaction;
use Vmeste\SaasBundle\Util\PaginationUtils;

/**
 * @Cache(expires="Thu, 19 Nov 1981 08:52:00 GMT", maxage=0, smaxage=0)
 */
class TransactionController extends Controller
{

    const PAYMENT_WRONG_HASH = "WRONG_HASH";
    const PAYMENT_PENDING = "PENDING";
    const PAYMENT_COMPLETED = "COMPLETED";
    const PAYMENT_UNCOMPLETED = "UNCOMPLETED";

    public function yandexCheckAction(Request $request)
    {

        if (!$request->isMethod('POST')) return;

        $ykShopId = $request->request->get('shopId');

        $em = $this->getDoctrine()->getManager();
        $yandexKassa = $em->getRepository('Vmeste\SaasBundle\Entity\YandexKassa')->findOneBy(array('shopId' => $ykShopId));

        $ykShopPassword = $yandexKassa->getShoppw();

        $hash = md5($request->request->get('action') . ';' . $request->request->get('orderSumAmount') . ';'
            . $request->request->get('orderSumCurrencyPaycash') . ';' . $request->request->get('orderSumBankPaycash') . ';'
            . $request->request->get('shopId') . ';' . $request->request->get('invoiceId') . ';'
            . $request->request->get('customerNumber') . ';' . $ykShopPassword);

        $paymentStatus = self::PAYMENT_PENDING;

        if (strcmp(strtolower($hash), strtolower($request->request->get('md5'))) === 0) {
            $code = 0;
        } else {
            $paymentStatus = self::PAYMENT_WRONG_HASH;
            $code = 1;
        }

        $postParamsArray = $this->get('request')->request->all();

        $requestDetails = $this->createRequestString($postParamsArray);

        $status = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'PENDING'));

        $donor = new Donor();
        $donor->setName($request->request->get('customerName', $request->request->get('orderNumber')));
        $donor->setEmail($request->request->get('customerEmail', ""));
        $donor->setDetails($request->request->get('customerComment', ""));
        $donor->setCurrency("RUB");
        $donor->setStatus($status);

        $em->persist($donor);

        $campaignId = $this->getCampaignId($request->request->get('orderNumber'));

        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $campaignId));

        $transaction = new Transaction();
        $transaction->setCampaign($campaign);
        $transaction->setDonor($donor);
        $transaction->setInvoiceId($request->request->get('invoiceId'));
        $transaction->setGross($request->request->get('orderSumAmount'));
        $transaction->setCurrency("RUB");
        $transaction->setPaymentStatus($paymentStatus);
        $transaction->setTransactionType("YMKassa: donate");
        $transaction->setTxnId($request->request->get('customerNumber'));
        $transaction->setDetails($requestDetails);

        $em->persist($transaction);

        $em->flush();

        $xml = new \DOMDocument('1.0', 'utf-8');
        $checkOrderResponse = $xml->createElement('checkOrderResponse');
        $checkOrderResponse->setAttribute('performedDatetime', $request->request->get('requestDatetime'));
        $checkOrderResponse->setAttribute('code', $code);
        $checkOrderResponse->setAttribute('invoiceId', $request->request->get('invoiceId'));
        $checkOrderResponse->setAttribute('shopId', $request->request->get('shopId'));
        $xml->appendChild($checkOrderResponse);
        $output = $xml->saveXML();

        $response = new Response($output, 200, array('content-type' => 'text/xml; charset=utf-8'));
        $response->send();

    }

    public function yandexPaymentAvisoAction(Request $request)
    {

        if (!$request->isMethod('POST')) return;

        $code = 0;
        $message = "Ok";

        $paymentStatus = self::PAYMENT_COMPLETED;
        $em = $this->getDoctrine()->getManager();

        $ykShopId = $request->request->get('shopId');
        $yandexKassa = $em->getRepository('Vmeste\SaasBundle\Entity\YandexKassa')->findOneBy(array('shopId' => $ykShopId));

        $ykShopPassword = $yandexKassa->getShoppw();

        if ($ykShopPassword) {

            $md5 = md5(
                $request->request->get('action') . ';' .
                $request->request->get('orderSumAmount') . ';' .
                $request->request->get('orderSumCurrencyPaycash') . ';' .
                $request->request->get('orderSumBankPaycash') . ';' .
                $request->request->get('shopId') . ';' .
                $request->request->get('invoiceId') . ';' .
                $request->request->get('customerNumber') . ';' .
                $ykShopPassword . ';');

            if (strcmp(strtolower($md5), strtolower($request->request->get('md5'))) === 0) {

                $invoiceId = $request->request->get('invoiceId');
                $transaction = $em->getRepository('Vmeste\SaasBundle\Entity\Transaction')->findOneBy(array('invoiceId' => $invoiceId));

                if ($transaction != null) {
                    $transaction->setPaymentStatus($paymentStatus);

                    $donor = $transaction->getDonor();
                    $status = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE'));
                    $donor->setStatus($status);

                    $em->persist($transaction);
                    $em->persist($donor);
                    $em->flush();

                    /**
                     *  Rebilling
                     */
                    if ($request->request->get('rebillingOn', false)) {

                        $payer_name = $donor->getName();
                        $payer_email = $donor->getEmail();

                        $campaignId = $this->getCampaignId($request->request->get('orderNumber'));
                        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $campaignId));

                        if ($campaign != null)
                            $campaign_title = $campaign->getTitle();


                        $txn_id = stripslashes($request->request->get('customerNumber'));
                        $gross_total = stripslashes($request->request->get('orderSumAmount'));
                        $amount = stripslashes($request->request->get('orderSumAmount'));

                        $payer_name = $payer_email = $campaign_title = "";
                        $gross_total = $txn_id = $amount = 0;


                        $mc_currency = "RUB";
                        $transaction_type = "donate";

                        if ($donor) {
                            // FIXME Andrei
                            //$rebilling = new Recurrent;

                            if ($request->request->get('rebillingOn') == false) {
                                // FIXME Andrei
                                //$rebilling->deleteRebilling($seller_id, $donor->getId());
                            } else {
                                // FIXME Andrei
//                            $params = array(
//                                'donor_id' => $donor->getId(),
//                                'campaign_id' => $seller_id,
//                                'clientOrderId' => 0,
//                                'invoiceId' => $request->request->get('invoiceId'),
//                                'amount' => $gross_total,
//                                'orderNumber' => $seller_id . '-0',
//                                'cvv' => '',
//                                'last_operation_time' => 0,
//                                'last_status' => 0,
//                                'last_error' => 0,
//                                'last_techMessage' => '',
//                                'subscription_date' => time(),
//                                'attempt' => 0,
//                                'success_date' => time()
//                            );
//                            $rebilling->insertRebilling($params);
//                            // Send the first notification email
//                            $rebilling->recurrent->email = $payer_email;
//                            $rebilling->recurrent->title = $campaign_title;
//                            $rebilling->notify_about_subscription();
                            }
                        }
                    } else {
                        $message = \Swift_Message::newInstance()
                            ->setSubject('Password recover on Vmeste')
                            ->setFrom($emailFrom = $this->container->getParameter('pass.recover.email.from'))
                            ->setTo($donor->getEmail())
                            ->setBody(
                                $this->renderView(
                                    'VmesteSaasBundle:Email:successfullPayment.html.twig',
                                    array(
                                        'name' => $donor->getName(),
                                        'amount' => $transaction->getAmount(),
                                        'yandexMoneyPage' =>
                                            $this->getRequest()->getHost() . "/"
                                            . $this->generateUrl('payment_page')
                                            . "/" . $transaction->getCampaign()->getId())
                                )
                            );
                        $this->get('mailer')->send($message);
                    }

                } else {
                    $logger = $this->get('logger');
                    $logger->error('Transaction with invoice id ' . $invoiceId . ' doesn\'t exist in Vmeste database');
                    $code = 200;
                    $message = "Unknown transaction";
                }
            } else {
                $code = 1;
                $message = 'Bad md5';
            }
        } else {
            $code = 200;
            $message = 'Bad shopPassword';
        }

        $xml = new \DOMDocument('1.0', 'utf-8');
        $paymentAvisoResponse = $xml->createElement('paymentAvisoResponse');
        $paymentAvisoResponse->setAttribute('performedDatetime', date('Y-m-d\TH:i:s.000P', time()));
        $paymentAvisoResponse->setAttribute('shopId', $request->request->get('shopId'));
        $paymentAvisoResponse->setAttribute('invoiceId', $request->request->get('invoiceId'));
        $paymentAvisoResponse->setAttribute('code', $code);
        $paymentAvisoResponse->setAttribute('message', $message);
        $xml->appendChild($paymentAvisoResponse);
        $output = $xml->saveXML();

        $response = new Response($output, 200, array('content-type' => 'text/xml; charset=utf-8'));
        $response->send();
    }

    /**
     * @Template
     */
    public function homeAction()
    {
        return array('data' => true);
    }

    /**
     * @Template
     */
    public function unsubscribeAction()
    {
        $recurrent_id = $this->getRequest()->query->get("recurrent");
        $invoice_id = $this->getRequest()->query->get("invoice");
        $response = array('error' => false,
            'recurrent' => $recurrent_id,
            'invoice' => $invoice_id,
            'title' => '',
            'intro' => '',
            'img' => '');

        if ($recurrent_id == null || $invoice_id == null) {
            $response['error'] = 'Неверные параметры';
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $recurrent = $em->getRepository('Vmeste\SaasBundle\Entity\Recurrent')->find($recurrent_id);
        if ($recurrent == null) {
            $response['error'] = 'Такой подписки не существует';
            return $response;
        }
        
        $donor = $recurrent->getDonor();
        if ($donor == null) {
            $response['error'] = 'Такой подписки не существует';
            return $response;
        }

        $campaignId = $donor->getCampaignId();
        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->find($campaignId);
        if ($campaign == null) {
            $response['error'] = 'Такой подписки не существует';
            return $response;
        }

        $response['title'] = $campaign->getTitle();
        $response['img'] = $campaign->getImage();
        $response['intro'] = $campaign->getFormIntro();


        if ($recurrent->getInvoiceId() != $invoice_id) {
            $response['error'] = 'Такой подписки не существует';
            return $response;
        }

        if ((int)$this->getRequest()->query->get("yes") == 1) {
        	$status = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'DELETED'));
        	$recurrent->setStatus($status);
        	$em->persist($recurrent);
            $em->flush();
            return $this->render('VmesteSaasBundle:Transaction:unsubscribe_success.html.twig', $response);
        } elseif ((int)$this->getRequest()->query->get("yes") == 2) {
			return $this->render('VmesteSaasBundle:Transaction:unsubscribe_decline.html.twig', $response);
		}

        return $response;


    }
    
    /**
     * @Template
     */
    public function subscribeAction()
    {
        $recurrent_id = $this->getRequest()->query->get("recurrent");
        $invoice_id = $this->getRequest()->query->get("invoice");
        $response = array('error' => false,
            'recurrent' => $recurrent_id,
            'invoice' => $invoice_id,
            'title' => '',
            'intro' => '',
            'img' => '');

		if ($recurrent_id == null || $invoice_id == null) {
            $response['error'] = 'Неверные параметры';
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $recurrent = $em->getRepository('Vmeste\SaasBundle\Entity\Recurrent')->find($recurrent_id);
        if ($recurrent == null) {
            $response['error'] = 'Такой подписки не существует';
            return $response;
        }
        
        $donor = $recurrent->getDonor();
        if ($donor == null) {
            $response['error'] = 'Такой подписки не существует';
            return $response;
        }

        $campaignId = $donor->getCampaignId();
        $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->find($campaignId);
        if ($campaign == null) {
            $response['error'] = 'Такой подписки не существует';
            return $response;
        }

        $response['title'] = $campaign->getTitle();
        $response['img'] = $campaign->getImage();
        $response['intro'] = $campaign->getFormIntro();


        if ($recurrent->getInvoiceId() != $invoice_id) {
            $response['error'] = 'Такой подписки не существует';
            return $response;
        }

        $status = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE'));
    	$recurrent->setStatus($status);
    	$em->persist($recurrent);
        $em->flush();

        return $this->render('VmesteSaasBundle:Transaction:unsubscribe_decline.html.twig', $response);
    }

    /**
     * @Template
     */
    public function reportAction()
    {

        $limit = $this->container->getParameter('paginator.page.items');
        $pageOnSidesLimit = 10;

        $page = $this->getRequest()->query->get("page", 1);

        $em = $this->getDoctrine()->getManager();

        $currentUser = $this->get('security.context')->getToken()->getUser();

        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $currentUser->getId()));

        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('t')->from('Vmeste\SaasBundle\Entity\Transaction', 't')
            ->innerJoin('Vmeste\SaasBundle\Entity\Campaign', 'c', 'WITH', 't.campaign = c');

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

    /**
     * @Template
     */
    public function searchAction()
    {

        if ($this->getRequest()->query->get("searchRequest", null) != null) {

            $searchRequest = $this->getRequest()->query->get("searchRequest", null);




            /*  $data = $request->get->all();
              $repo = $this->getDoctrine()
                  ->getRepository('PfBlogBundle:Article');
              $query = $repo->createQueryBuilder('a')
                  ->where('a.title LIKE :title')
                  ->setParameter('title', '%'.$data['search'].'%')
                  ->getQuery();
              $paginator  = $this->get('knp_paginator');
              $pagination = $paginator->paginate(
                  $query->getResults(),//get the results here
                  $this->requrest->get('page',1),
                  4
              );

              payername payeremail*/


            $limit = $this->container->getParameter('paginator.page.items');
            $pageOnSidesLimit = 2;

            $page = $this->getRequest()->query->get("page", 1);

            $em = $this->getDoctrine()->getManager();

            $currentUser = $this->get('security.context')->getToken()->getUser();

            $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $currentUser->getId()));

            $queryBuilder = $em->createQueryBuilder();

            $queryBuilder->select('t')->from('Vmeste\SaasBundle\Entity\Transaction', 't')
                ->innerJoin('Vmeste\SaasBundle\Entity\Donor', 'd', 'WITH', 't.donor = d')
                ->innerJoin('Vmeste\SaasBundle\Entity\Campaign', 'c', 'WITH', 't.campaign = c')
                ->where('d.name LIKE :name OR d.email LIKE :email')
                ->setParameter('name', '%' . $searchRequest . '%')
                ->setParameter('email', '%' . $searchRequest . '%');


            $queryBuilder->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

            $paginator = new Paginator($queryBuilder, $fetchJoinCollection = false);

            $totalItems = count($paginator);

            $pageCount = (int)ceil($totalItems / $limit);

            $pageNumberArray = PaginationUtils::generatePaginationPageNumbers($page, $pageOnSidesLimit, $pageCount);


            return array(
                'transactions' => $paginator,
                'pages' => $pageNumberArray,
                'page' => $page,
                'searchRequest' => $searchRequest
            );
        } else {
            return $this->redirect($this->generateUrl('transaction_report'));
        }


    }

    public function reportExportAction()
    {

        $startTimestamp = $endTimestamp = 0;

        if ($this->getRequest()->query->get("start", null) != null
            && $this->getRequest()->query->get("end", null) != null
        ) {
            $start = $this->getRequest()->query->get("start");
            $end = $this->getRequest()->query->get("end");
            $startTimestamp = $this->parseDateToTimestamp($start);
            $endTimestamp = $this->parseDateToTimestamp($end);
        }

        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('t')->from('Vmeste\SaasBundle\Entity\Transaction', 't')
            ->innerJoin('Vmeste\SaasBundle\Entity\Campaign', 'c', 'WITH', 't.campaign = c');

        if ($startTimestamp != 0 && $endTimestamp != 0) {
            $queryBuilder->andWhere('t.created >= ?1');
            $queryBuilder->andWhere('t.created <= ?2');
            $queryBuilder->setParameter(1, $startTimestamp);
            $queryBuilder->setParameter(2, $endTimestamp);
        }

        $report = $queryBuilder->getQuery()->getResult();

        $responseHeaders = array();

        if (strstr($this->getRequest()->server->get('HTTP_USER_AGENT'), "MSIE")) {
            $responseHeaders['pragma'] = 'public';
            $responseHeaders['expires'] = '0';
            $responseHeaders['cache-control'] = 'must-revalidate, post-check=0, pre-check=0';
            $responseHeaders['content-type'] = 'text/csv; charset=utf-8';
            $responseHeaders['content-disposition'] = 'attachment; filename="export-transactions' . date("Y-m-d") . '.csv"';
            $responseHeaders['content-transfer-encoding'] = 'binary';
        } else {
            $responseHeaders['content-type'] = 'text/csv; charset=utf-8';
            $responseHeaders['content-disposition'] = 'attachment; filename="export-transactions' . date("Y-m-d") . '.csv"';
        }

        $separator = ';';

        if ($separator == 'tab') $separator = "\t";

        $output = chr(0xEF).chr(0xBB).chr(0xBF).'"Проект"' . $separator
            . '"Дата платежа"' . $separator
            . '"ФИО"' . $separator
            . '"Email"' . $separator
            . '"Способ оплаты"' . $separator
            . '"Сумма"' . $separator
            . '"Признак подписчика"' . $separator
            . '"Комментарии"' . $separator . "\r\n";

        foreach ($report as $transaction) {
            $output .= '"'
                . str_replace('"', '', $transaction->getCampaign()->getTitle()) . '"' . $separator . '"'
                . str_replace('"', '', date("Y-m-d H:i:s", $transaction->getCreated())) . '"' . $separator . '"'
                . str_replace('"', '', $transaction->getDonor()->getName()) . '"' . $separator . '"'
                . str_replace('"', "", $transaction->getDonor()->getEmail()) . '"' . $separator . '"'
                . str_replace('"', "", $transaction->getTransactionType()) . '"' . $separator . '"'
                . str_replace('"', "", $transaction->getGross()) . '"' . $separator . '"';
            $transaction->getDonor()->getRecurrent() != null ? $output .= '1' : $output .= '0';
            $output .= '"' . $separator . '"' . str_replace('"', "", $transaction->getDonor()->getDetails()) . '"' . "\r\n";
        }

        $response = new Response($output, 200, $responseHeaders);
        return $response;
    }

    /**
     * @param $postParamsArray
     * @return string
     */
    private function createRequestString($postParamsArray)
    {
        $response = "";
        foreach ($postParamsArray as $key => $value) {
            $value = urlencode(stripslashes($value));
            $response .= "&" . $key . "=" . $value;
        }
        return $response;
    }

    /**
     * @param $orderNumber
     * @return mixed
     */
    private function getCampaignId($orderNumber)
    {
        $orderNumberExploded = explode("-", $orderNumber);
        $campaignId = $orderNumberExploded[0];
        return $campaignId;
    }

    /**
     * @param $dateStr
     * @return int
     */
    private function parseDateToTimestamp($dateStr)
    {
        $a = date_parse_from_format('Y-m-d', $dateStr);
        $timestamp = mktime(0, 0, 0, $a['month'], $a['day'], $a['year']);
        return $timestamp;
    }
} 