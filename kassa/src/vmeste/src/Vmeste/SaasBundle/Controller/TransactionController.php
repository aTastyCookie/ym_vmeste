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
use Vmeste\SaasBundle\Entity\Recurrent;
use Vmeste\SaasBundle\Entity\Transaction;
use Vmeste\SaasBundle\Entity\SysEvent;
use Vmeste\SaasBundle\Util\PaginationUtils;
use Vmeste\SaasBundle\Util\Rebilling;
use Vmeste\SaasBundle\Util\Clear;

/**
 * @Cache(expires="Thu, 19 Nov 1981 08:52:00 GMT", maxage=0, smaxage=0)
 */
class TransactionController extends Controller
{

    const PAYMENT_WRONG_HASH = "WRONG_HASH";
    const PAYMENT_WRONG_SHOPID = "WRONG_SHOPID";
    const PAYMENT_PENDING = "PENDING";
    const PAYMENT_COMPLETED = "COMPLETED";
    const PAYMENT_UNCOMPLETED = "UNCOMPLETED";

    public function yandexCheckAction(Request $request)
    {

        if (!$request->isMethod('POST'))  {
            throw $this->createNotFoundException();
        }

        $code = 0;
        $message = "Ok";

        $ykShopId = Clear::integer($request->request->get('shopId'));

        $em = $this->getDoctrine()->getManager();
        $yandexKassa = $em->getRepository('Vmeste\SaasBundle\Entity\YandexKassa')->findOneBy(array('shopId' => $ykShopId));
        if (!$yandexKassa) {
            $code = 100;
            $message = "Incorrect shopId";
        } else {
            $ykShopPassword = $yandexKassa->getShoppw();

            $hash = md5($request->request->get('action') . ';' . $request->request->get('orderSumAmount') . ';'
                . $request->request->get('orderSumCurrencyPaycash') . ';' . $request->request->get('orderSumBankPaycash') . ';'
                . $request->request->get('shopId') . ';' . $request->request->get('invoiceId') . ';'
                . $request->request->get('customerNumber') . ';' . $ykShopPassword);

            $paymentStatus = self::PAYMENT_PENDING;

            $orderNumber = Clear::string_without_quotes($request->request->get('orderNumber'));

            if (strcmp(strtolower($hash), strtolower($request->request->get('md5'))) !== 0) {
                $code = 1;
                $message = 'Bad md5';
            } else {
                $campaignId = $this->getCampaignId($orderNumber);
                $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $campaignId));

                if ($campaign != null) {
                    $postParamsArray = $this->get('request')->request->all();

                    $requestDetails = $this->createRequestString($postParamsArray);

                    $status = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'PENDING'));

                    $amount = Clear::number($request->request->get('orderSumAmount'));

                    $invoiceId = Clear::string_without_quotes($request->request->get('invoiceId'));

                    $transaction = $em->getRepository('Vmeste\SaasBundle\Entity\Transaction')->findOneBy(array('invoiceId' => $invoiceId));

                    if($transaction) {
                        $donor = $transaction->getDonor();
                        $em->remove($transaction);
                        $em->remove($donor);
                        $em->flush();
                    }

                    $donorId = $this->getDonorId($orderNumber);
                    if($donorId) {
                        $donor = $em->getRepository('Vmeste\SaasBundle\Entity\Donor')->findOneBy(array('id' => $donorId));
                    } else {
                        $donor = new Donor();
                        $donor->setName(
                            Clear::string_without_quotes(
                                $request->request->get('customerName', $request->request->get('orderNumber'))
                            )
                        );
                        $donor->setEmail(Clear::string_without_quotes($request->request->get('customerEmail', "")));
                        $donor->setCampaignId($campaignId);
                        $donor->setDetails(Clear::string_without_quotes($request->request->get('customerComment', "")));
                        $donor->setCurrency("RUB");
                        $donor->setStatus($status);
                        $donor->setAmount($amount);
                        $donor->setDates();
                        $em->persist($donor);
                    }

                    $transaction = new Transaction();
                    $transaction->setCampaign($campaign);
                    $transaction->setDonor($donor);
                    $transaction->setInvoiceId($invoiceId);
                    $transaction->setGross($amount);
                    $transaction->setCurrency("RUB");
                    $transaction->setPaymentStatus($paymentStatus);
                    $transaction->setTransactionType(Clear::string_without_quotes($request->request->get('paymentType')));
                    $transaction->setDetails($requestDetails);
                    $em->persist($transaction);
                    $em->flush();

                    $sysEvent = new SysEvent();
                    $sysEvent->setUserId(0);
                    $sysEvent->setEvent(SysEvent::CHANGE_TRANSACTION_PAYMENT_STATUS . ' InvoiceId: '. $transaction->getInvoiceId() . ' ' . $paymentStatus);
                    $sysEvent->setIp($this->container->get('request')->getClientIp());
                    $eventTracker = $this->get('sys_event_tracker');
                    $eventTracker->track($sysEvent);

                } else {
                    $code = 200;
                    $message = "Incorrect campaing";
                }
            }
        }

        $xml = new \DOMDocument('1.0', 'utf-8');
        $checkOrderResponse = $xml->createElement('checkOrderResponse');
        $checkOrderResponse->setAttribute('performedDatetime', $request->request->get('requestDatetime'));
        $checkOrderResponse->setAttribute('code', $code);
        $checkOrderResponse->setAttribute('invoiceId', $request->request->get('invoiceId'));
        $checkOrderResponse->setAttribute('shopId', $request->request->get('shopId'));
        $checkOrderResponse->setAttribute('message', $message);
        $xml->appendChild($checkOrderResponse);
        $output = $xml->saveXML();

        $sysEvent = new SysEvent();
        $sysEvent->setUserId(0);
        $sysEvent->setEvent(SysEvent::CREATE_TRANSACTION . ' ' . $output);
        $sysEvent->setIp($this->container->get('request')->getClientIp());
        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);

        $response = new Response($output, 200, array('content-type' => 'text/xml; charset=utf-8'));
        return $response;

    }

    public function yandexPaymentAvisoAction(Request $request)
    {

        if (!$request->isMethod('POST')) {
            throw $this->createNotFoundException();
        }

        $paymentStatus = null;

        $code = 0;
        $message = "Ok";
        $em = $this->getDoctrine()->getManager();

        $ykShopId = Clear::integer($request->request->get('shopId'));
        $yandexKassa = $em->getRepository('Vmeste\SaasBundle\Entity\YandexKassa')->findOneBy(array('shopId' => $ykShopId));

        if (!$yandexKassa) {
            $code = 200;
            $message = "Incorrect shopId";
        } else {
            $ykShopPassword = $yandexKassa->getShoppw();

            if ($ykShopPassword) {

                $requestString = $request->request->get('action') . ';' . $request->request->get('orderSumAmount') . ';'
                    . $request->request->get('orderSumCurrencyPaycash') . ';' . $request->request->get('orderSumBankPaycash') . ';'
                    . $request->request->get('shopId') . ';' . $request->request->get('invoiceId') . ';'
                    . $request->request->get('customerNumber') . ';' . $ykShopPassword;

                $hash = md5($requestString);

                if (strcmp(strtolower($hash), strtolower($request->request->get('md5'))) === 0) {

                    $invoiceId = $request->request->get('invoiceId');
                    $sysEvent = new SysEvent();
                    $sysEvent->setUserId(0);
                    $sysEvent->setEvent('Searching transaction by InvoiceId: ' . $invoiceId);
                    $sysEvent->setIp($this->container->get('request')->getClientIp());
                    $eventTracker = $this->get('sys_event_tracker');
                    $eventTracker->track($sysEvent);

                    $transaction = $em->getRepository('Vmeste\SaasBundle\Entity\Transaction')->findOneBy(
                        array('invoiceId' => $invoiceId));

                    $sysEvent = new SysEvent();
                    $sysEvent->setUserId(0);
                    $sysEvent->setEvent('Transaction: ' . gettype($transaction));
                    $sysEvent->setIp($this->container->get('request')->getClientIp());
                    $eventTracker->track($sysEvent);

                    if ($transaction != null) {
                        $transaction->setPaymentStatus(self::PAYMENT_COMPLETED);

                        $sysEvent = new SysEvent();
                        $sysEvent->setUserId(0);
                        $sysEvent->setEvent(SysEvent::CHANGE_TRANSACTION_PAYMENT_STATUS . ' InvoiceId: '.
                            $transaction->getInvoiceId() . ' ' . self::PAYMENT_COMPLETED);
                        $sysEvent->setIp($this->container->get('request')->getClientIp());
                        $eventTracker = $this->get('sys_event_tracker');
                        $eventTracker->track($sysEvent);

                        $donor = $transaction->getDonor();
                        $status = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE'));
                        $donor->setStatus($status);

                        $em->persist($transaction);
                        $em->persist($donor);
                        $em->flush();

                        $userSettingsArray = $transaction->getCampaign()->getUser()->getSettings();
                        $settings = $userSettingsArray[0];
                        $emailFrom = $settings->getSenderEmail();

                        $sysEvent = new SysEvent();
                        $sysEvent->setUserId(0);
                        $sysEvent->setEvent(SysEvent::UPDATE_TRANSACTION . ' LINE: ' . __LINE__);
                        $sysEvent->setIp($this->container->get('request')->getClientIp());
                        $eventTracker = $this->get('sys_event_tracker');
                        $eventTracker->track($sysEvent);

                        /**
                         *  Rebilling
                         */
                        $rb = $request->request->get('rebillingOn', false);
                        if($rb === 'false') $rb = false;
                        $time = time();

                        // Search for existing recurrent
                        $existingRecurrent = $em->getRepository('Vmeste\SaasBundle\Entity\Recurrent')->findOneBy(
                            array('donor' => $donor));
                        if($existingRecurrent) {
                            $orderNumber = Clear::string_without_quotes($request->request->get('orderNumber'));
                            //$existingRecurrent->setInvoiceId($transaction->getInvoiceId());
                            $existingRecurrent->setOrderNumber($orderNumber);
                            $existingRecurrent->setSuccessDate($time);
                            $em->persist($existingRecurrent);
                            $em->flush();

                            $sysEvent = new SysEvent();
                            $sysEvent->setUserId(0);
                            $sysEvent->setEvent(SysEvent::UPDATE_TRANSACTION . ' LINE: ' . __LINE__);
                            $sysEvent->setIp($this->container->get('request')->getClientIp());
                            $eventTracker = $this->get('sys_event_tracker');
                            $eventTracker->track($sysEvent);
                        }

                        if ($rb) {
                            $sysEvent = new SysEvent();
                            $sysEvent->setUserId(0);
                            $sysEvent->setEvent(SysEvent::UPDATE_TRANSACTION . ' LINE: ' . __LINE__);
                            $sysEvent->setIp($this->container->get('request')->getClientIp());
                            $eventTracker = $this->get('sys_event_tracker');
                            $eventTracker->track($sysEvent);

                            $campaignId = Clear::integer($this->getCampaignId($request->request->get('orderNumber')));
                            $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $campaignId));
                            $userSettingsArray = $campaign->getUser()->getSettings();
                            $settings = $userSettingsArray[0];

                            $amount = Clear::string_without_quotes(
                                number_format((float)stripslashes($request->request->get('orderSumAmount')), 2)
                            );
                            $pan = $request->request->get('cdd_pan_mask');

                            $recurrent = new Recurrent();
                            $recurrent->setAmount($amount);
                            $recurrent->setCampaign($campaign);
                            $recurrent->setClientOrderId(0);
                            $recurrent->setCvv('');
                            $recurrent->setPan($pan);
                            $recurrent->setDonor($donor);
                            $recurrent->setInvoiceId($invoiceId);
                            $recurrent->setLastOperationTime($time);
                            $recurrent->setLastStatus(0);
                            $recurrent->setLastError(0);
                            $recurrent->setLastTechmessage('');
                            $recurrent->setOrderNumber($campaignId . '-' . $time);
                            $recurrent->setStatus($status);
                            $recurrent->setSubscriptionDate($time);
                            $recurrent->setSuccessDate($time);
                            $day = date('j');
                            $month = date('n') + 1;
                            $year = date('Y');
                            if($month > 12) {
                                $month = 1;
                                $year += 1;
                            }
                            if($day>28) $day = 28;
                            $recurrent->setNextDate(mktime(12, 0, 0, $month, $day, $year));
                            $em->persist($recurrent);
                            $em->flush();

                            // Send the first notification email
                            $rebilling = new Rebilling(
                                array('icpdo' => $em,
                                    'url_unsubcribe'=> $this->container->getParameter('recurrent.apphost') ,
                                    'url_subcribe'=> $this->container->getParameter('recurrent.apphost'),
                                    'context' => $this,
                                    'context_mailer' => $this->get('mailer'))
                            );
                            $payer_email = $donor->getEmail();
                            $rebilling->recurrent->email = $payer_email;
                            $rebilling->recurrent->emailFrom = $emailFrom;
                            $rebilling->recurrent->fond = $settings->getCompanyName();
                            $rebilling->recurrent->sum = $amount;
                            $rebilling->recurrent->id = $recurrent->getId();
                            $rebilling->recurrent->invoice = $invoiceId;
                            $rebilling->notify_about_subscription();

                            $sysEvent = new SysEvent();
                            $sysEvent->setUserId(0);
                            $sysEvent->setEvent(SysEvent::UPDATE_TRANSACTION . ' LINE: ' . __LINE__);
                            $sysEvent->setIp($this->container->get('request')->getClientIp());
                            $eventTracker = $this->get('sys_event_tracker');
                            $eventTracker->track($sysEvent);

                        } else {
                            $sysEvent = new SysEvent();
                            $sysEvent->setUserId(0);
                            $sysEvent->setEvent(SysEvent::UPDATE_TRANSACTION . ' LINE: ' . __LINE__);
                            $sysEvent->setIp($this->container->get('request')->getClientIp());
                            $eventTracker = $this->get('sys_event_tracker');
                            $eventTracker->track($sysEvent);

                            $mailMessage = \Swift_Message::newInstance()
                                ->setSubject('Спасибо за помощь!')
                                ->setFrom($emailFrom)
                                ->setTo($donor->getEmail())
                                ->setBody(
                                    $this->renderView(
                                        'VmesteSaasBundle:Email:successfullPayment.html.twig',
                                        array(
                                            'name' => $donor->getName(),
                                            'amount' => $transaction->getGross(),
                                            'fond' => $settings->getCompanyName(),
                                            'yandexMoneyPage' =>
                                                $this->container->getParameter('recurrent.apphost')
                                                . $transaction->getCampaign()->getUrl())
                                    )
                                );
                            $this->get('mailer')->send($mailMessage);
                        }

                        $sysEvent = new SysEvent();
                        $sysEvent->setUserId(0);
                        $sysEvent->setEvent(SysEvent::UPDATE_TRANSACTION . ' LINE: ' . __LINE__);
                        $sysEvent->setIp($this->container->get('request')->getClientIp());
                        $eventTracker = $this->get('sys_event_tracker');
                        $eventTracker->track($sysEvent);

                        $paymentStatus = $transaction->getPaymentStatus();

                    } else {
                        $sysEvent = new SysEvent();
                        $sysEvent->setUserId(0);
                        $sysEvent->setEvent(SysEvent::UPDATE_TRANSACTION . ' Transaction with invoice id ' . $invoiceId . ' doesn\'t exist in Vmeste database');
                        $sysEvent->setIp($this->container->get('request')->getClientIp());
                        $eventTracker = $this->get('sys_event_tracker');
                        $eventTracker->track($sysEvent);
                        $code = 200;
                        $message = "Unknown transaction";
                    }
                } else {
                    $code = 1;
                    $message = 'Bad md5';
                    $sysEvent = new SysEvent();
                    $sysEvent->setUserId(0);
                    $sysEvent->setEvent(SysEvent::UPDATE_TRANSACTION . ' ' . $message);
                    $sysEvent->setIp($this->container->get('request')->getClientIp());
                    $eventTracker = $this->get('sys_event_tracker');
                    $eventTracker->track($sysEvent);
                }
            } else {
                $code = 200;
                $message = 'Bad shopPassword';
                $sysEvent = new SysEvent();
                $sysEvent->setUserId(0);
                $sysEvent->setEvent(SysEvent::UPDATE_TRANSACTION . ' ' . $message);
                $sysEvent->setIp($this->container->get('request')->getClientIp());
                $eventTracker = $this->get('sys_event_tracker');
                $eventTracker->track($sysEvent);
            }
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

        $sysEvent = new SysEvent();
        $sysEvent->setUserId(0);
        $sysEvent->setEvent(SysEvent::UPDATE_TRANSACTION . ' paymentAviso status '. $paymentStatus . '; output: ' . $output);
        $sysEvent->setIp($this->container->get('request')->getClientIp());
        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);

        $response = new Response($output, 200, array('content-type' => 'text/xml; charset=utf-8'));
        return $response;
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
        $recurrent_id = Clear::integer($this->getRequest()->query->get("recurrent"));
        $invoice_id = Clear::integer($this->getRequest()->query->get("invoice"));
        $response = array('error' => false,
            'recurrent' => $recurrent_id,
            'invoice' => $invoice_id,
            'title' => '',
            'intro' => '',
            'img' => '');

        $sysEvent = new SysEvent();
        $sysEvent->setUserId(0);
        $sysEvent->setEvent(SysEvent::UNSUBSCRIBE_RECURRENT . " request $recurrent_id $invoice_id");
        $sysEvent->setIp($this->container->get('request')->getClientIp());
        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);

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

            $sysEvent->setEvent(SysEvent::UNSUBSCRIBE_RECURRENT . " done $recurrent_id $invoice_id");
            $eventTracker->track($sysEvent);

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
        $recurrent_id = Clear::integer($this->getRequest()->query->get("recurrent"));
        $invoice_id = Clear::integer($this->getRequest()->query->get("invoice"));
        $response = array('error' => false,
            'recurrent' => $recurrent_id,
            'invoice' => $invoice_id,
            'title' => '',
            'intro' => '',
            'img' => '');


        $sysEvent = new SysEvent();
        $sysEvent->setUserId(0);
        $sysEvent->setEvent(SysEvent::SUBSCRIBE_RECURRENT . " request $recurrent_id $invoice_id");
        $sysEvent->setIp($this->container->get('request')->getClientIp());
        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);

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

        $sysEvent->setEvent(SysEvent::SUBSCRIBE_RECURRENT . " done $recurrent_id $invoice_id");
        $eventTracker->track($sysEvent);

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

        $user = $this->get('security.context')->getToken()->getUser();
        $sysEvent = new SysEvent();
        $sysEvent->setUserId($user->getId());
        $sysEvent->setEvent(SysEvent::REPORT_ALL_TRANSACTIONS);
        $sysEvent->setIp($this->container->get('request')->getClientIp());
        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);

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

            $limit = $this->container->getParameter('paginator.page.items');
            $pageOnSidesLimit = 2;

            $page = $this->getRequest()->query->get("page", 1);

            $em = $this->getDoctrine()->getManager();

            $currentUser = $this->get('security.context')->getToken()->getUser();

            $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $currentUser->getId()));

            $user = $this->get('security.context')->getToken()->getUser();
            $sysEvent = new SysEvent();
            $sysEvent->setUserId($user->getId());
            $sysEvent->setEvent(SysEvent::SEARCH_TRANSACTION);
            $sysEvent->setIp($this->container->get('request')->getClientIp());
            $eventTracker = $this->get('sys_event_tracker');
            $eventTracker->track($sysEvent);

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

        $user = $this->get('security.context')->getToken()->getUser();
        $sysEvent = new SysEvent();
        $sysEvent->setUserId($user->getId());
        $sysEvent->setEvent(SysEvent::REPORT_TRANSACTIONS_BY_DATE);
        $sysEvent->setIp($this->container->get('request')->getClientIp());
        $eventTracker = $this->get('sys_event_tracker');
        $eventTracker->track($sysEvent);

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

        $output = chr(0xEF) . chr(0xBB) . chr(0xBF) . '"Проект"' . $separator
            . '"ФИО"' . $separator
            . '"Email"' . $separator
            . '"Сумма"' . $separator
            . '"Дата платежа"' . $separator
            . '"Способ оплаты"' . $separator
            . '"Статус"' . $separator
            . '"Признак подписчика"' . $separator
            . '"Комментарии"' . $separator . "\r\n";

        foreach ($report as $transaction) {
            $output .= '"'
                . str_replace('"', '', $transaction->getCampaign()->getTitle()) . '"' . $separator . '"'
                . str_replace('"', '', $transaction->getDonor()->getName()) . '"' . $separator . '"'
                . str_replace('"', "", $transaction->getDonor()->getEmail()) . '"' . $separator . '"'
                . str_replace('"', "", $transaction->getGross()) . '"' . $separator . '"'
                . str_replace('"', '', date("Y-m-d H:i:s", $transaction->getCreated())) . '"' . $separator . '"'
                . str_replace('"', "", $transaction->getTransactionType()) . '"' . $separator . '"'
                . str_replace('"', "", $transaction->getPaymentStatus()) . '"' . $separator . '"';
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
        $campaignId = false;
        $orderNumberExploded = explode("-", $orderNumber);
        if(is_array($orderNumberExploded) && isset($orderNumberExploded[0])) $campaignId = $orderNumberExploded[0];
        return $campaignId;
    }

    /**
     * @param $orderNumber
     * @return mixed
     */
    private function getDonorId($orderNumber)
    {
        $donorId = false;
        $orderNumberExploded = explode("-", $orderNumber);
        if(is_array($orderNumberExploded) && count($orderNumberExploded) == 3 && isset($orderNumberExploded[1]))
            $donorId = $orderNumberExploded[1];
        return $donorId;
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