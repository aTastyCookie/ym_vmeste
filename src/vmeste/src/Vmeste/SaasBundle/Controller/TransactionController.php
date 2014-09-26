<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/25/14
 * Time: 11:54 PM
 */

namespace Vmeste\SaasBundle\Controller;


use Doctrine\Tests\Common\Annotations\Fixtures\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vmeste\SaasBundle\Entity\Transaction;

class TransactionController extends Controller
{

    public function yandexCheckAction(Request $request)
    {

        if (!$request->isMethod('POST')) return;


        $ykShopId = $request->request->get('shopId');

        $em = $this->getDoctrine()->getManager();
        $yandexKassa = $em->getRepository('Vmeste\SaasBundle\Entity\YandexKassa')->findOneBy(array('shopId' => $ykShopId));

        $ykShoppassword = $yandexKassa->getShoppw();

        $hash = md5($request->request->get('action') . ';' . $request->request->get('orderSumAmount') . ';'
            . $request->request->get('orderSumCurrencyPaycash') . ';' . $request->request->get('orderSumBankPaycash') . ';'
            . $request->request->get('shopId') . ';' . $request->request->get('invoiceId') . ';'
            . $request->request->get('customerNumber') . ';' . $ykShoppassword);

        if (strcmp(strtolower($hash), strtolower($request->request->get('md5'))) === 0) {

            //$shoppassword = $options['yandex_shoppw'];
            //$hash = md5($_POST['action'] . ';' . $_POST['orderSumAmount'] . ';' . $_POST['orderSumCurrencyPaycash'] . ';' . $_POST['orderSumBankPaycash'] . ';' . $_POST['shopId'] . ';' . $_POST['invoiceId'] . ';' . $_POST['customerNumber'] . ';' . $shoppassword);
            //if (strtolower($hash) != strtolower($_POST['md5'])) {

            $code = 1;

            print '<?xml version="1.0" encoding="UTF-8"?>';
            print '<checkOrderResponse performedDatetime="' . $_POST['requestDatetime'] . '" code="' . $code . '"' . ' invoiceId="' . $_POST['invoiceId'] . '" shopId="' . $_POST['shopId'] . '"/>';

            $postParamsArray = $this->get('request')->request->all();

            $response = "";
            foreach ($postParamsArray as $key => $value) {
                $value = urlencode(stripslashes($value));
                $response .= "&" . $key . "=" . $value;
            }

            $donor = $em->getRepository('Vmeste\SaasBundle\Entity\Donor')->findOneBy(array('id' => $request->request->get('noIDcustomerNumber')));

            $paymentStatus = "Uncomplete";

            $transaction = new Transaction();
            $transaction->setDonor($donor); // $request->request->get('noIDcustomerNumber') == $item_number = intval($_POST['noIDcustomerNumber']);
            $transaction->setPayerName($request->request->get('orderNumber')); //$payer_name = stripslashes($_POST['orderNumber']);
            $transaction->setPayerEmail($request->request->get('orderNumber')); // $payer_email = stripslashes($_POST['orderNumber']);
            $transaction->setGross($request->request->get('orderSumAmount')); // $gross_total = stripslashes($_POST['orderSumAmount']);
            $transaction->setCurrency("RUB"); // $mc_currency = stripslashes("RUB");
            $transaction->setPaymentStatus($paymentStatus); // $payment_status = "Uncomplete";
            $transaction->setTransactionType("YMKassa: donate"); // $transaction_type = "donate";
            $transaction->setTxnId($request->request->get('customerNumber')); // $txn_id = stripslashes($_POST['customerNumber']);
            $transaction->setDetails($response);


            $em->persist($transaction);

            // TODO Find usage
            // $seller_id = stripslashes($_POST['orderNumber']);
            // $amount = stripslashes($_POST['orderSumAmount']);


            if ($paymentStatus == "Completed") {

                $statusActive = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE')); // $icdb->query("UPDATE " . $icdb->prefix . "donors SET status = '" . STATUS_ACTIVE . "' WHERE id = '" . $item_number . "'");
                $donor->setStatus($statusActive);
                $em->persist($donor);
                $em->flush();

                // TODO Send email
//                $tags = array("{payer_name}", "{payer_email}", "{gross}", "{currency}", "{campaign_title}", "{transaction_date}", "{gateway}");
//                $vals = array($payer_name, $payer_email, $gross_total, $mc_currency, $campaign_title, date("Y-m-d H:i:s") . " (server time)", "YMKassa");
//                send_thanksgiving_email($tags, $vals, $payer_email);
            } else {

                $statusPending = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'PENDING')); // $icdb->query("UPDATE " . $icdb->prefix . "donors SET status = '" . STATUS_PENDING . "' WHERE id = '" . $item_number . "'");
                $donor->setStatus($statusPending);
                $em->persist($donor);
                $em->flush();

                // TODO Send email
//                $tags = array("{payer_name}", "{payer_email}", "{gross}", "{currency}", "{campaign_title}", "{payment_status}", "{transaction_date}", "{gateway}");
//                $vals = array($payer_name, $payer_email, $gross_total, $mc_currency, $campaign_title, $payment_status, date("Y-m-d H:i:s") . " (server time)", "YMKassa");
//                send_failed_email($tags, $vals, $payer_email);
            }

        } else {
            $code = 0;
            print '<?xml version="1.0" encoding="UTF-8"?>';
            print '<checkOrderResponse performedDatetime="' . $_POST['requestDatetime'] . '" code="' . $code . '"' . ' invoiceId="' . $_POST['invoiceId'] . '" shopId="' . $_POST['shopId'] . '"/>';


            $postParamsArray = $this->get('request')->request->all();

            $response = "";
            foreach ($postParamsArray as $key => $value) {
                $value = urlencode(stripslashes($value));
                $response .= "&" . $key . "=" . $value;
            }

            $donor = $em->getRepository('Vmeste\SaasBundle\Entity\Donor')->findOneBy(array('id' => $request->request->get('noIDcustomerNumber')));

            $paymentStatus = "Pending";

            $transaction = new Transaction();
            $transaction->setDonor($donor); // $request->request->get('noIDcustomerNumber') == $item_number = intval($_POST['noIDcustomerNumber']);
            $transaction->setPayerName($donor->getName()); //$payer_name = stripslashes($_POST['orderNumber']);
            $transaction->setPayerEmail($donor->getEmail()); // $payer_email = stripslashes($_POST['orderNumber']);
            $transaction->setGross($request->request->get('orderSumAmount')); // $gross_total = stripslashes($_POST['orderSumAmount']);
            $transaction->setCurrency("RUB"); // $mc_currency = stripslashes("RUB");
            $transaction->setPaymentStatus($paymentStatus); // $payment_status = "Pending";
            $transaction->setTransactionType("YMKassa: donate"); // $transaction_type = "donate";
            $transaction->setTxnId($request->request->get('customerNumber')); // $txn_id = stripslashes($_POST['customerNumber']);
            $transaction->setDetails($response);

            $em->persist($transaction);

            if ($paymentStatus == "Completed") {

                $statusActive = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE')); // $icdb->query("UPDATE " . $icdb->prefix . "donors SET status = '" . STATUS_ACTIVE . "' WHERE id = '" . $item_number . "'");
                $donor->setStatus($statusActive);
                $em->persist($donor);
                $em->flush();

                // TODO Send email
//                $tags = array("{payer_name}", "{payer_email}", "{gross}", "{currency}", "{campaign_title}", "{transaction_date}", "{gateway}");
//                $vals = array($payer_name, $payer_email, $gross_total, $mc_currency, $campaign_title, date("Y-m-d H:i:s") . " (server time)", "YMKassa");
//                send_thanksgiving_email($tags, $vals, $payer_email);


            } else {


                $statusPending = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'PENDING')); // $icdb->query("UPDATE " . $icdb->prefix . "donors SET status = '" . STATUS_PENDING . "' WHERE id = '" . $item_number . "'");
                $donor->setStatus($statusPending);
                $em->persist($donor);
                $em->flush();

                // TODO Send email
//                $tags = array("{payer_name}", "{payer_email}", "{gross}", "{currency}", "{campaign_title}", "{payment_status}", "{transaction_date}", "{gateway}");
//                $vals = array($payer_name, $payer_email, $gross_total, $mc_currency, $campaign_title, $payment_status, date("Y-m-d H:i:s") . " (server time)", "YMKassa");
            }

        }

        /*$response = new Response($output, 200, array('content-type' => 'text/xml; charset=utf-8'));
        $response->send();*/
    }

    public function yandexPaymentAvisoAction(Request $request)
    {


        if (!$request->isMethod('POST')) return;

        // XML-related routine
        $xml = new \DOMDocument('1.0', 'utf-8');
        $paymentAvisoResponse = $xml->createElement('paymentAvisoResponse');
        $paymentAvisoResponse->setAttribute('performedDatetime', date('Y-m-d\TH:i:s.000P', time()));
        $paymentAvisoResponse->setAttribute('shopId', $request->request->get('shopId'));
        $paymentAvisoResponse->setAttribute('invoiceId', $request->request->get('invoiceId'));

        $ykShopId = $request->request->get('shopId');

        $em = $this->getDoctrine()->getManager();
        $yandexKassa = $em->getRepository('Vmeste\SaasBundle\Entity\YandexKassa')->findOneBy(array('shopId' => $ykShopId));

        $ykShoppassword = $yandexKassa->getShoppw();


        $response = $item_number = $payer_name = $payer_email = $campaign_title = "";
        $gross_total = $txn_id = $amount = 0;


        $mc_currency = "RUB";
        $transaction_type = "donate";

        foreach ($_POST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $response .= "&" . $key . "=" . $value;
        }

        if ($ykShoppassword) {
            $md5 = md5(
                $request->request->get('action') . ';' .
                $request->request->get('orderSumAmount') . ';' .
                $request->request->get('orderSumCurrencyPaycash') . ';' .
                $request->request->get('orderSumBankPaycash') . ';' .
                $request->request->get('shopId') . ';' .
                $request->request->get('invoiceId') . ';' .
                $request->request->get('customerNumber') . ';' .
                $ykShoppassword . ';');

            $item_number = intval($request->request->get('noIDcustomerNumber'));

            $donor = $em->getRepository('Vmeste\SaasBundle\Entity\Donor')->findOneBy(array('id' => $request->request->get('noIDcustomerNumber')));

            if ($donor != null) {
                $payer_name = $donor->getName();
                $payer_email = $donor->getEmail();
            }

            $seller_id = explode("-", $request->request->get('orderNumber'));
            $seller_id = $seller_id[0];

            $txn_id = stripslashes($request->request->get('customerNumber'));
            $gross_total = stripslashes($request->request->get('orderSumAmount'));
            $amount = stripslashes($request->request->get('orderSumAmount'));


            if (strcmp(strtolower($md5), strtolower($request->request->get('md5'))) === 0) {

                $paymentAvisoResponse->setAttribute('code', 0);

                $campaign = $em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('id' => $seller_id));

                if ($campaign != null) $campaign_title = $campaign->getTitle();

                if ($request->request->get('rebillingOn', false)) {
                    // Add the first record about rebilling

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
                        $payment_status = "Completed";
                    }
                }
            } else {
                $paymentAvisoResponse->setAttribute('code', 1);
                $paymentAvisoResponse->setAttribute('message', 'Bad md5');
                $payment_status = "Uncompleted";
            }
        } else {
            $paymentAvisoResponse->setAttribute('code', 200);
            $paymentAvisoResponse->setAttribute('message', 'Bad shopPassword');
            $payment_status = "Uncompleted";
        }


        $transaction = new Transaction();
        $transaction->setDonor($donor); // $request->request->get('noIDcustomerNumber') == $item_number = intval($_POST['noIDcustomerNumber']);
        $transaction->setPayerName($donor->getName()); //$payer_name = stripslashes($_POST['orderNumber']);
        $transaction->setPayerEmail($donor->getEmail()); // $payer_email = stripslashes($_POST['orderNumber']);
        $transaction->setGross($request->request->get('orderSumAmount')); // $gross_total = stripslashes($_POST['orderSumAmount']);
        $transaction->setCurrency("RUB"); // $mc_currency = stripslashes("RUB");
        $transaction->setPaymentStatus($payment_status); // $payment_status = "Pending";
        $transaction->setTransactionType("YMKassa: donate"); // $transaction_type = "donate";
        $transaction->setTxnId($request->request->get('customerNumber')); // $txn_id = stripslashes($_POST['customerNumber']);
        $transaction->setDetails($response);

        $em->persist($transaction);

        if ($payment_status == "Completed") {

            $statusActive = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'ACTIVE')); // $icdb->query("UPDATE " . $icdb->prefix . "donors SET status = '" . STATUS_ACTIVE . "' WHERE id = '" . $item_number . "'");
            $donor->setStatus($statusActive);
            $em->persist($donor);
            $em->flush();

            // TODO Send email
//                $tags = array("{payer_name}", "{payer_email}", "{gross}", "{currency}", "{campaign_title}", "{transaction_date}", "{gateway}");
//                $vals = array($payer_name, $payer_email, $gross_total, $mc_currency, $campaign_title, date("Y-m-d H:i:s") . " (server time)", "YMKassa");
//                send_thanksgiving_email($tags, $vals, $payer_email);


        } else {


            $statusPending = $em->getRepository('Vmeste\SaasBundle\Entity\Status')->findOneBy(array('status' => 'PENDING')); // $icdb->query("UPDATE " . $icdb->prefix . "donors SET status = '" . STATUS_PENDING . "' WHERE id = '" . $item_number . "'");
            $donor->setStatus($statusPending);
            $em->persist($donor);
            $em->flush();

            // TODO Send email
//                $tags = array("{payer_name}", "{payer_email}", "{gross}", "{currency}", "{campaign_title}", "{payment_status}", "{transaction_date}", "{gateway}");
//                $vals = array($payer_name, $payer_email, $gross_total, $mc_currency, $campaign_title, $payment_status, date("Y-m-d H:i:s") . " (server time)", "YMKassa");
//                send_failed_email($tags, $vals, $payer_email);
        }

        $xml->appendChild($paymentAvisoResponse);
        $output = $xml->saveXML();


        $response = new Response($output, 200, array('content-type' => 'text/xml; charset=utf-8'));
        $response->send();
    }
} 