<?php
/**
 * Created by PhpStorm.
 * User: Glyatsevich_a
 * Date: 17.12.2014
 * Time: 10:30
 */
namespace Vmeste\SaasBundle\Util;

use Vmeste\SaasBundle\Entity\Recurrent;
use Vmeste\SaasBundle\Entity\Transaction;
use Vmeste\SaasBundle\Entity\Donor;
use Vmeste\SaasBundle\Entity\Campaign;
use Vmeste\SaasBundle\Entity\YandexKassa;
use Vmeste\SaasBundle\Entity\SysEvent;
use Vmeste\SaasBundle\Util\Rebilling;

class RebillingTest extends Rebilling {
    const SSL_VERIFYPEER = false;
    const SSL_VERIFYHOST = 2;
    const USERAGENT = 'Ymoney Vmeste';
    const CONNECTTIMEOUT = 30;
    const HTTPHEADER = 'application/x-www-form-urlencoded';
    const NO_ERROR = 0;
    const LIMIT_ROWS = 100;

    public $ymurl;
    public $path_to_cert;
    public $path_to_key;
    public $cert_pass;
    public $icpdo;
    public $recurrent;
    public $data;
    public $status_blocked;
    public $status_id_blocked;
    public $status_id_deleted;
    public $status_id_active;
    public $context;
    public $context_mailer;
    public $apphost;

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

    public function recurrent_test()
    {
        $output_array = array('clientOrderId' => 132465,
            'invoiceId' => '321321321321',
            'amount' => 100,
            'orderNumber' => '1232466287');
        $url = $this->context->getParameter('recurrent.ymurl');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(self::HTTPHEADER));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, self::SSL_VERIFYPEER);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, self::SSL_VERIFYHOST);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->context->getParameter('recurrent.cert_path'));
        curl_setopt($ch, CURLOPT_SSLKEY, $this->context->getParameter('recurrent.key_path'));
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->context->getParameter('recurrent.cert_pass'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($output_array));
        echo  "Request $url : " . http_build_query($output_array) . "\n";
        $result = curl_exec($ch);
        echo "Result string: $result \n";
        echo "Result curl_getinfo: \n";
        print_r(curl_getinfo($ch));
        echo "\n";
        curl_close($ch);

        //echo "Search transaction \n";
        //$transaction = $this->icpdo->getRepository('Vmeste\SaasBundle\Entity\Transaction')->findOneBy(
        //    array('invoiceId' => '2000000298154'));
        //print_r($transaction);

        /*$message = \Swift_Message::newInstance()
            ->setSubject('Спасибо за помощь!')
            ->setFrom('robimgut@gmail.com')
            ->setTo('robimgut@gmail.com')
            ->setBody('test message'
            /*$this->context_adapter->renderView(
                'VmesteSaasBundle:Email:successfullPaymentMonthly.html.twig',
                array(
                    'amount' => 40,
                    'unsubscribe' => 'http://unsub.ru',
                    'fond' => 'my')
            )*/
        //    );*/
        //echo $this->context_mailer->getLocalDomain()."\n";
        //echo $this->context_mailer->send($message)."\n";
        //echo "End \n";
        //echo __FILE__.": ".__LINE__."\n";
    }
}

