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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vmeste\SaasBundle\Entity\Donor;
use Vmeste\SaasBundle\Entity\Transaction;
use Vmeste\SaasBundle\Util\PaginationUtils;

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
	  *@Template
	*/
    public function homeAction()
    {
        return array('data' => true);
    }
    
    /**
	  *@Template
	*/
    public function unsubscribeAction()
    {
		return array('data' => true);
	}

    /**
     * @Template
     */
    public function reportAction()
    {

        $limit = 2;
        $pageOnSidesLimit = 2;

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

        if ($this->getRequest()->query->get("reccurent", 0) == 1)
            $reccurent = 'your-value'; // FIXME Andrei

        $em = $this->getDoctrine()->getManager();

        $currentUser = $this->get('security.context')->getToken()->getUser();

        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $currentUser->getId()));

        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('t')->from('Vmeste\SaasBundle\Entity\Transaction', 't')
            ->innerJoin('Vmeste\SaasBundle\Entity\Campaign', 'c', 'WITH', 't.campaign = c')
            ->where('c.user = ?1')
            ->setParameter(1, $user);

        $report = $queryBuilder->getQuery()->getResult();

        $responseHeaders = array();

        if (strstr($this->getRequest()->server->get('HTTP_USER_AGENT'), "MSIE")) {
            $responseHeaders['pragma'] = 'public';
            $responseHeaders['expires'] = '0';
            $responseHeaders['cache-control'] = 'must-revalidate, post-check=0, pre-check=0';
            $responseHeaders['content-type'] = 'application-download';
            $responseHeaders['content-disposition'] = 'attachment; filename="export-donors' . $recurrent . '.csv"';
            $responseHeaders['content-transfer-encoding'] = 'binary';
        } else {
            $responseHeaders['content-type'] = 'application-download';
            $responseHeaders['content-disposition'] = 'attachment; filename="export-donors' . $recurrent . '.csv"';
        }

        $settings = $user->getSettings();
        $userSettings = $settings[0];
        $separator = $userSettings->getCsvColumnSeparator();

        if ($separator == 'tab') $separator = "\t";

        $output = '"Project"' . $separator . '"FIO"' . $separator . '"E-Mail"' . $separator . '"Recurrent"' . "\r\n";

        foreach ($report as $transaction) {
            $output .= '"' . str_replace('"', '', $transaction->getCampaign()->getTitle()) . '"' . $separator . '"'
                . str_replace('"', '', $transaction->getDonor()->getName()) . '"' . $separator . '"'
                . str_replace('"', "", $transaction->getDonor()->getEmail()) . '"' . "\r\n";
            //. str_replace('"', "", $row["recurrent"]) . '"' . "\r\n"; // FIXME Andrei
        }

        $response = new Response($output, 200, $responseHeaders);
        $response->send();
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
} 