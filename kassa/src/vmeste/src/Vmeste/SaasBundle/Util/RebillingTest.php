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
    public function recurrent_test()
    {
        $output_array = array('clientOrderId' => 132465,
            'invoiceId' => '321321321321',
            'amount' => 100,
            'orderNumber' => '1232466287');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->ymurl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(self::HTTPHEADER));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->context->getParameter('recurrent.cert_path'));
        curl_setopt($ch, CURLOPT_SSLKEY, $this->context->getParameter('recurrent.key_path'));
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->context->getParameter('recurrent.cert_pass'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($output_array));
        echo  "Request: " . http_build_query($output_array) . "\n";
        $result = curl_exec($ch);
        echo "Result: \n";
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

