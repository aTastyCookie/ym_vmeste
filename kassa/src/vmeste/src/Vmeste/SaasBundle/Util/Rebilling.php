<?php
namespace Vmeste\SaasBundle\Util;

use Vmeste\SaasBundle\Entity\Recurrent;
use Vmeste\SaasBundle\Entity\Transaction;
use Vmeste\SaasBundle\Entity\Donor;
use Vmeste\SaasBundle\Entity\Campaign;
use Vmeste\SaasBundle\Entity\YandexKassa;

class Rebilling
{
    const SSL_VERIFYPEER = false;
    const SSL_VERIFYHOST = false;
    const USERAGENT = 'Ymoney Vmeste';
    const CONNECTTIMEOUT = 30;
    const HTTPHEADER = 'application/x-www-form-urlencoded';
    const NO_ERROR = 0;
    const LIMIT_ROWS = 100;

    private $ymurl;
    private $path_to_cert;
    private $path_to_key;
    private $cert_pass;
    private $icpdo;
    private $url_unsubcribe;
    private $url_subcribe;
    public $recurrent;
    public $data;
    private $status_blocked;
    private $status_id_blocked;
    private $status_id_deleted;
    private $status_id_active;
    private $context;
    private $context_mailer;
    private $apphost;

    public function __construct($params = array())
    {
        if (!empty($params))
            foreach ($params as $key => $param)
                $this->$key = $param;
        else return false;

        $this->recurrent = new \stdClass;
        $this->status_blocked = $this->icpdo
            ->getRepository('Vmeste\SaasBundle\Entity\Status')
            ->findOneBy(array('status' => 'BLOCKED'));
        $this->status_id_blocked = $this->status_blocked->getId();
        $this->status_id_deleted = $this->icpdo
            ->getRepository('Vmeste\SaasBundle\Entity\Status')
            ->findOneBy(array('status' => 'DELETED'));
        $this->status_id_deleted = $this->status_id_deleted->getId();
        $this->status_id_active = $this->icpdo
            ->getRepository('Vmeste\SaasBundle\Entity\Status')
            ->findOneBy(array('status' => 'ACTIVE'));
        $this->status_id_active = $this->status_id_active->getId();

        if (!$this->apphost) $this->apphost = "https://vmeste.yandex.ru/";
    }

    public function notify()
    {
        $today_d = date("j");
        $today_m = date("n");
        $today_y = date("Y");
        $today_t = date("t");
        $today_start = mktime(0, 0, 0, $today_m, $today_d, $today_y) + 259200;
        $today_end = mktime(23, 59, 59, $today_m, $today_d, $today_y) + 259200;

        $this->attempt_notify($today_start, $today_end);

        return true;
    }

    public function run()
    {
        $today_d = date("j");
        $today_m = date("n");
        $today_y = date("Y");
        $today_start = mktime(0, 0, 0, $today_m, $today_d, $today_y);
        $today_end = mktime(23, 59, 59, $today_m, $today_d, $today_y);

        $this->attempt_send($today_start, $today_end);
        return true;
    }

    private function send_money(Recurrent $recur)
    {
        $orderId = time() . rand(1, 1000);
        $amount = $recur->getAmount();
        $campaign = $recur->getCampaign();
        $orderNumber = $campaign->getId() . '-' . $orderId;
        $output_array = array('clientOrderId' => $orderId,
            'invoiceId' => $recur->getInvoiceId(),
            'amount' => $amount,
            'orderNumber' => $orderNumber);
        if ($recur->getCvv()) $output_array['cvv'] = $recur->getCvv();

        $donor = $recur->getDonor(true);
        if (!$donor) {
            $recur->setStatus($this->status_blocked);
            $this->icpdo->persist($recur);
            $this->icpdo->flush();
            return false;
        }

        if ($campaign == null || $campaign->getStatus()->getStatus() === 'BLOCKED') {
            $recur->setStatus($this->status_blocked);
            $this->icpdo->persist($recur);
            $this->icpdo->flush();
            return false;
        }

        $emailTo = $donor->getEmail();
        $settings = $campaign->getUser()->getSettings();
        $userSettings = $settings[0];
        $fond = $userSettings->getCompanyName();
        $emailFrom = $userSettings->getSenderEmail();
        $unsubscribe = $this->url_unsubcribe .
            '?recurrent=' . $recur->getId() .
            '&invoice=' . $recur->getInvoiceId();

        echo "Sending " . $amount . " to " . $orderNumber . "\n";

        $yandexKassa = $userSettings->getYandexKassa();
        $sandboxMode = $yandexKassa->getSandbox();

        if($sandboxMode == YandexKassa::SANDBOX_ENABLED)
            $this->ymurl = $this->context->getParameter('sandbox.recurrent.ymurl');

        /*$xml = new \DOMDocument();
        $element = $xml->createElement('repeatCardPaymentRequest');
        $clientOrderId = $xml->createAttribute('clientOrderId');
        $clientOrderId->value = $output_array['clientOrderId'];
        $invoiceId = $xml->createAttribute('invoiceId');
        $invoiceId->value = $output_array['invoiceId'];
        $amount = $xml->createAttribute('amount');
        $amount->value = $output_array['amount'];
        $orderNumber = $xml->createAttribute('orderNumber');
        $orderNumber->value = $output_array['orderNumber'];
        $element->appendChild($clientOrderId);
        $element->appendChild($invoiceId);
        $element->appendChild($amount);
        $element->appendChild($orderNumber);
        if(isset($output_array['cvv'])) {
            $cvv =  $xml->createAttribute('cvv');
            $cvv->value = $output_array['cvv'];
            $element->appendChild($cvv);
        }
        $xml->appendChild($element);*/

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->ymurl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(self::HTTPHEADER));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($ch, CURLOPT_SSLCERT, $yandexKassa->getUploadRootDir() . $yandexKassa->getCertFilePath());
        //curl_setopt($ch, CURLOPT_SSLKEY, $yandexKassa->getUploadRootDir() . $yandexKassa->getCertKeyFilePath());
        //curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $yandexKassa->getCertPass());
        curl_setopt($ch, CURLOPT_SSLCERT, $this->context->getParameter('recurrent.cert_path'));
        curl_setopt($ch, CURLOPT_SSLKEY, $this->context->getParameter('recurrent.key_path'));
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->context->getParameter('recurrent.cert_pass'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($output_array));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $xml->saveXML());
        echo  "Request: " . http_build_query($output_array) . "\n";
        $result = curl_exec($ch);
        curl_close($ch);

        echo  "Result: " . $result . "\n";

        if (!empty($result) && $result != false) {

            $xml->loadXML($result);
            $result = array();
            $responses = $xml->getElementsByTagName('repeatCardPaymentResponse');
            foreach ($responses as $response) {
                $result['error'] = $response->getAttribute('error');
                $result['status'] = $response->getAttribute('status');
                $result['techMessage'] = $response->getAttribute('techMessage');
            }
        } else {
            $result['techMessage'] = 'no connection';
            $result['error'] = 1000;
        }

        if ($result['error'] == 0) {
            // Insert transaction
            $transaction = new Transaction();
            $transaction->setDates();
            $transaction->setCurrency("RUB");
            $transaction->setDetails(json_encode($result));
            $transaction->setDonor($donor);
            $transaction->setGross(floatval($amount));
            $transaction->setPaymentStatus("Completed");
            $transaction->setTransactionType("YMKassa: donate");
            $transaction->setCampaign($campaign);
            $transaction->setInvoiceId($recur->getInvoiceId());
            $this->icpdo->persist($transaction);
            $this->icpdo->flush();

            $day = date('j');
            $month = date('n') + 1;
            $year = date('Y');
            if ($month > 12) {
                $month = 1;
                $year += 1;
            }
            if ($day > 28) $day = 28;
            $recur->setNextDate(mktime(12, 0, 0, $month, $day, $year));
            $recur->setSuccessDate(time());
            $recur->setClientOrderId($orderId);
            $recur->setOrderNumber($orderNumber);
            $recur->setLastOperationTime(time());
            $recur->setLastTechmessage($result['techMessage']);
            $recur->setLastError($result['error']);
            $this->icpdo->persist($recur);
            $this->icpdo->flush();

            $this->notify_about_successfull_monthly_payment($emailTo, $emailFrom, $fond, $amount, $unsubscribe);
        } else {
            // NEW DATE = TOMORROW
            $day = date('j') + 1;
            $month = date('n');
            $year = date('Y');
            if ($day > 28) {
                $day = 1;
                $month += 1;
                if ($month > 12) {
                    $month = 1;
                    $year += 1;
                }
            }
            $recur->setNextDate(mktime(12, 0, 0, $month, $day, $year));
            $recur->setLastOperationTime(time());
            $recur->setLastTechmessage($result['techMessage']);
            $recur->setLastError($result['error']);
            $this->icpdo->persist($recur);
            $this->icpdo->flush();

            //$this->notify_about_successfull_monthly_payment($emailTo, $emailFrom, $fond, $amount, $unsubscribe);
        }
    }

    private function attempt_send($today_start, $today_end)
    {
        $offset = 0;
        $this->_next_send_data($offset, $today_start, $today_end);

        while (!empty($this->data)) {
            foreach ($this->data as $recur) {
                $this->send_money($recur);
            }
            $offset += self::LIMIT_ROWS;
            $this->_next_send_data($offset, $today_start, $today_end);
        }
    }

    private function attempt_notify($today_start, $today_end)
    {
        $offset = 0;
        $this->_next_send_data($offset, $today_start, $today_end);

        while (!empty($this->data)) {
            foreach ($this->data as $recur) {
                $campaign = $recur->getCampaign();
                $userSettingsArray = $campaign->getUser()->getSettings();
                $settings = $userSettingsArray[0];
                $emailFrom = $settings->getSenderEmail();
                $emailTo = $recur->getDonor()->getEmail();

                $message = \Swift_Message::newInstance()
                    ->setSubject('Через три дня мы получим ваше пожертвование')
                    ->setFrom($emailFrom)
                    ->setTo($emailTo)
                    ->setBody(
                        $this->context->renderView(
                            'VmesteSaasBundle:Email:notifyaboutPayment.html.twig',
                            array(
                                'amount' => $recur->getAmount(),
                                'unsubscribe' => $this->url_unsubcribe .
                                    '?recurrent=' . $recur->getId() .
                                    '&invoice=' . $recur->getInvoiceId(),
                                'fond' => $settings->getCompanyName(),
                                'pan' => $recur->getPan())
                        )
                    );
                $this->context_mailer->send($message);
            }
            $offset += self::LIMIT_ROWS;
            $this->_next_data($offset, $monthdays, $today_start, $today_end);
        }
    }

    public function notify_about_subscription()
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Спасибо за помощь!')
            ->setFrom($this->recurrent->emailFrom)
            ->setTo($this->recurrent->email)
            ->setBody(
                $this->context->renderView(
                    'VmesteSaasBundle:Email:successfullSubscription.html.twig',
                    array(
                        'amount' => $this->recurrent->sum,
                        'unsubscribe' => $this->url_unsubcribe .
                            '?recurrent=' . $this->recurrent->id .
                            '&invoice=' . $this->recurrent->invoice,
                        'fond' => $this->recurrent->fond)
                )
            );
        $this->context_mailer->send($message);
    }

    private function notify_about_successfull_monthly_payment($emailTo, $emailFrom, $fond, $amount, $unsubscribe)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Спасибо за помощь!')
            ->setFrom($emailFrom)
            ->setTo($emailTo)
            ->setBody(
                $this->context->renderView(
                    'VmesteSaasBundle:Email:successfullPaymentMonthly.html.twig',
                    array(
                        'amount' => $amount,
                        'unsubscribe' => $unsubscribe,
                        'fond' => $fond)
                )
            );
        $this->context_mailer->send($message);
    }

    private function _next_data($offset, $today_start, $today_end)
    {

        $queryBuilder = $this->icpdo->createQueryBuilder();

        $queryBuilder->select('r')->from('Vmeste\SaasBundle\Entity\Recurrent', 'r')
            ->innerJoin('Vmeste\SaasBundle\Entity\Donor', 'd', 'WITH', ' r.donor = d')
            ->innerJoin('Vmeste\SaasBundle\Entity\Campaign', 'c', 'WITH', 'r.campaign = c')
            ->where('c.status = :statusIdActive')
            ->andWhere('d.status = :statusIdActive')
            ->andWhere('r.next_date >= :todayStart')
            ->andWhere('r.next_date <= :todayEnd')
            ->setParameter('statusIdActive', $this->status_id_active)
            ->setParameter('todayStart', $today_start)
            ->setParameter('todayEnd', $today_end)
            ->orderBy('r.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(self::LIMIT_ROWS);

        $query = $queryBuilder->getQuery();
        $this->data = $query->getResult();

    }

    private function _next_send_data($offset, $today_start, $today_end)
    {
        $queryBuilder = $this->icpdo->createQueryBuilder();

        $queryBuilder->select('r')->from('Vmeste\SaasBundle\Entity\Recurrent', 'r')
            ->innerJoin('Vmeste\SaasBundle\Entity\Donor', 'd', 'WITH', ' r.donor = d')
            ->innerJoin('Vmeste\SaasBundle\Entity\Campaign', 'c', 'WITH', 'r.campaign = c')
            ->where('c.status = :statusIdActive')
            ->andWhere('d.status = :statusIdActive')
            ->andWhere('r.next_date >= :todayStart')
            ->andWhere('r.next_date <= :todayEnd')
            ->setParameter('statusIdActive', $this->status_id_active)
            ->setParameter('todayStart', $today_start)
            ->setParameter('todayEnd', $today_end)
            ->orderBy('r.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(self::LIMIT_ROWS);

        $query = $queryBuilder->getQuery();
        $this->data = $query->getResult();

    }
}

?>