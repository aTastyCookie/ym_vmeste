<?php
namespace Vmeste\SaasBundle\Util;
use Vmeste\SaasBundle\Entity\Recurrent;
use Vmeste\SaasBundle\Entity\Transaction;
use Vmeste\SaasBundle\Entity\Donor;
use Vmeste\SaasBundle\Entity\Campaign;

class Rebilling {
	const SSL_VERIFYPEER = false;
	const SSL_VERIFYHOST = false;
	const USERAGENT = 'Ymoney Vmeste';
	const CONNECTTIMEOUT = 30;
	const HTTPHEADER = 'application/x-www-form-urlencoded';
	const NO_ERROR = 0;
	const LIMIT_ROWS = 100;
	const MONTH = 2592000;
	const MONTH33 = 2851200;
	const MONTH36 = 3110400;
	const MONTH39 = 3369600;
	const DAYS_BEFORE = 259200;
	
	private $ymurl;
	private $path_to_cert;
	private $path_to_key;
	private $cert_pass;
	private $icpdo;
	private $url_unsubcribe;
	private $url_subcribe;
	public 	$recurrent;
	public 	$data;
    private $status_blocked;
    private $status_id_blocked;
    private $status_id_deleted;
    private $status_id_active;
    private $context;
    private $apphost;
	
	public function __construct($params = array()) {
		if(!empty($params)) 
			foreach($params as $key=>$param)
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

        if(!$this->apphost) $this->apphost = "http://vmeste/";
	}
	
	public function notify()
	{
		$time = time();
		$today_d = date("j", $time);
		$today_m = date("n", $time);
		$today_y = date("Y", $time);
		$today_start = mktime(0, 0, 0, $today_m, $today_d, $today_y);
		$today_end = mktime(23, 59, 59, $today_m, $today_d, $today_y);

		// First notification
		$this->attempt_notify(0, $today_start, $today_end, self::MONTH);
			
		// Second notification
		$this->attempt_notify(1, $today_start, $today_end, self::MONTH33);
		
		// Third notification
		$this->attempt_notify(2, $today_start, $today_end, self::MONTH36);
		
		//Fourth notification
		$this->attempt_notify(3, $today_start, $today_end, self::MONTH39);
			
		return true;
	}
	
	public function run()
	{
		$time = time();
		$today_d = date("j", $time);
		$today_m = date("n", $time);
		$today_y = date("Y", $time);
		$today_start = mktime(0, 0, 0, $today_m, $today_d, $today_y);
		$today_end = mktime(23, 59, 59, $today_m, $today_d, $today_y);
		
		$this->attempt_send(0, $today_start, $today_end, self::MONTH);
		$this->attempt_send(1, $today_start, $today_end, self::MONTH33);
		$this->attempt_send(2, $today_start, $today_end, self::MONTH36);
		$this->attempt_send(3, $today_start, $today_end, self::MONTH39);
		return true;
	}
	
	private function send_money(Recurrent $recur)
	{
        $orderId = time();
        $amount = $recur->getAmount();
        $orderNumber = $recur->getCampaignId() . '-' . $orderId . rand(1, 1000);
        $output_array = array('clientOrderId'=>$orderId,
        						'invoiceId'=>$recur->getInvoiceId(),
        						'amount'=>$amount,
        						'orderNumber'=>$orderNumber);
        if($recur->getCvv()) $output_array['cvv'] = $recur->getCvv();

        $donor = $recur->getDonor(true);
        if(!$donor) {
            $recur->setStatus($this->status_blocked);
            $this->icpdo->persist($recur);
            $this->icpdo->flush();
            return false;
        }

        $campaign = $this->icpdo->getRepository('Vmeste\SaasBundle\Entity\Campaign')
                                ->findOneBy(array('id' => $recur->getCampaignId()));

        if($campaign == null || $campaign->getStatus()->getStatus() === 'BLOCKED') {
            $recur->setStatus($this->status_blocked);
            $this->icpdo->persist($recur);
            $this->icpdo->flush();
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->ymurl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(self::HTTPHEADER));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->path_to_cert);
        curl_setopt($ch, CURLOPT_SSLKEY, $this->path_to_key);
		curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->cert_pass);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($output_array));
        $result = curl_exec($ch);
        curl_close($ch);
        //echo $result, PHP_EOL;
        $xml = new \DOMDocument();
		$xml->loadXML($result);
		$result = array();
		$responses = $xml->getElementsByTagName('repeatCardPaymentResponse');
		foreach($responses as $response) {
			$result['error'] = $response->getAttribute('error');
			$result['status'] = $response->getAttribute('status');
			$result['techMessage'] = $response->getAttribute('techMessage');
		}

        $emailTo = $donor->getEmail();
        $settings = $campaign->getUser()->getSettings();
        $userSettings = $settings[0];
        $fond = $userSettings->getCompanyName();
        $emailFrom = $settings->getSenderEmail();

		if($result['error'] == 0) {
			// Insert transaction
			$transaction = new Transaction();
			$transaction->setDates();
			$transaction->setCurrency("RUB");
			$transaction->setDetails(mysql_real_escape_string(json_encode($result)));
			$transaction->setDonor($donor);
			$transaction->setGross(floatval($amount));
			$transaction->setPaymentStatus("Completed");
            $transaction->setTransactionType("YMKassa: donate");
            $transaction->setCampaign($campaign);
            $transaction->setInvoiceId($recur->getInvoiceId());
            $this->icpdo->persist($transaction);
            $this->icpdo->flush();

			$attempt = 0;
			$success_date = time();

            $unsubscribe = $this->url_unsubcribe .
                            '?recurrent=' . $recur->getId() .
                            '&invoice=' . $recur->getInvoiceId();
            $this->notify_about_successfull_monthly_payment($emailTo, $emailFrom, $fond, $amount, $unsubscribe);
		} else {
			if($recur->getAttempt() == 3) {
                $campaign_url =  $this->apphost . $campaign->getUrl();
				$this->notify_about_auto_deleting($emailTo, $fond, $campaign_url, $amount, $recur->getPan(), $emailFrom);
                $recur->setStatus($this->status_blocked);
                $this->icpdo->persist($recur);
                $this->icpdo->flush();
				$attempt = 4;
			} else {
				$attempt = $recur->getAttempt() + 1;
				$success_date = $recur->getSuccessDate();
			}
		}
		
		if($attempt<4) {
            $recur->setClientOrderId($orderId);
            $recur->setOrderNumber($orderNumber);
            $recur->setAttempt($attempt);
            $recur->setSuccessDate($success_date);
            $recur->setLastOperationTime(time());
            $recur->setLastStatus($result['status']);
            $recur->setLastTechmessage($result['techMessage']);
            $recur->setLastError($result['error']);
            $this->icpdo->persist($recur);
            $this->icpdo->flush();
		}
	}
	
	private function attempt_send($attempt, $today_start, $today_end, $monthdays)
	{
        $offset = 0;
        $this->data = $this->_next_send_data($offset, $monthdays, $today_start, $today_end, $attempt);
		while(!empty($this->data)){
			foreach($this->data as $recur) {
				$this->send_money($recur);
			}
            $offset += self::LIMIT_ROWS;
            $this->data = $this->_next_send_data($offset, $monthdays, $today_start, $today_end, $attempt);
		}
	}
	
	private function attempt_notify($attempt, $today_start, $today_end, $monthdays)
	{
        $offset = 0;
        $this->data = $this->_next_data($offset, $monthdays, $today_start, $today_end, $attempt);

        while(!empty($this->data)){
            foreach($this->data as $recur) {
                $campaignId = $recur->getCampaignId();
                $campaign = $this->icpdo->getRepository('Vmeste\SaasBundle\Entity\Campaign')
                                ->findOneBy(array('id' => $campaignId));
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
                $this->context->get('mailer')->send($message);
            }
            $offset += self::LIMIT_ROWS;
            $this->data = $this->_next_data($offset, $monthdays, $today_start, $today_end, $attempt);
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
        $this->context->get('mailer')->send($message);
	}
	
	private function notify_about_auto_deleting($email, $fond, $campaign_url, $amount, $pan, $from)
	{
        $message = \Swift_Message::newInstance()
            ->setSubject('Мы отменили ваши ежемесячные пожертвования')
            ->setFrom($from)
            ->setTo($email)
            ->setBody(
                $this->context->renderView(
                    'VmesteSaasBundle:Email:autodeletingSubscription.html.twig',
                    array(
                        'sum' => $amount,
                        'campaign_url' => $campaign_url,
                        'fond' => $fond,
                        'pan' => $pan)
                )
            );
        $this->context->get('mailer')->send($message);
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
        $this->context->get('mailer')->send($message);
    }

	private function _next_data($offset, $monthdays, $today_start, $today_end, $attempt)
	{
	    $queryBuilder = $this->icpdo->createQueryBuilder();
	    $queryBuilder
                    ->select('*')
                    ->from('Vmeste\SaasBundle\Entity\Recurrent','r')
                    ->where('c.status_id = '.$this->status_id_active)
                    ->andWhere('d.status_id = '.$this->status_id_active)
                    ->andWhere('(r.success_date + '.$monthdays.' - '.self::DAYS_BEFORE.')>='.$today_start)
                    ->andWhere('(r.success_date + '.$monthdays.' - '.self::DAYS_BEFORE.')<='.$today_end)
                    ->andWhere('r.attempt = '.$attempt)
                    ->innerJoin('Vmeste\SaasBundle\Entity\Donor', 'd', 'WITH', 'r.donor_id = d.id')
                    ->innerJoin('Vmeste\SaasBundle\Entity\Campaign', 'c', 'WITH', 'r.campaign_id = c.id')
	                ->orderBy('r.id', 'ASC')
                    ->setFirstResult( $offset )
                    ->setMaxResults( self::LIMIT_ROWS );
        $statement = $queryBuilder->execute();
        $this->data = $statement->fetchAll();
        /* $query = $this->icpdo->createQuery('SELECT *
                                    FROM Vmeste\SaasBundle\Entity\Recurrent r
                                    INNER JOIN Vmeste\SaasBundle\Entity\Donor d WITH (r.donor_id = d.id)
                                    INNER JOIN Vmeste\SaasBundle\Entity\Campaign c WITH (r.campaign_id = c.id)
                                    WHERE c.status_id = '.$this->status_id_active.'
                                    AND d.status_id = '.$this->status_id_active.'
			                        AND (r.success_date + '.$monthdays.' - '.self::DAYS_BEFORE.')>='.$today_start.'
			                        AND (r.success_date + '.$monthdays.' - '.self::DAYS_BEFORE.')<='.$today_end.'
			                        AND r.attempt = '.$attempt.'
			                        ORDER BY r.id ASC LIMIT :offset, :limit;')
                            ->setParameter('offset', $offset)
                            ->setParameter('limit', self::LIMIT_ROWS);
        $this->data = $query->getResult(); */
	}

    private function _next_send_data($offset, $monthdays, $today_start, $today_end, $attempt)
    {
        $queryBuilder = $this->icpdo->createQueryBuilder();
        $queryBuilder
                    ->select('*')
                    ->from('Vmeste\SaasBundle\Entity\Recurrent','r')
                    ->where('c.status_id = '.$this->status_id_active)
                    ->andWhere('d.status_id = '.$this->status_id_active)
                    ->andWhere('(r.success_date + '.$monthdays.')>='.$today_start)
                    ->andWhere('(r.success_date + '.$monthdays.')<='.$today_end)
                    ->andWhere('r.attempt = '.$attempt)
                    ->innerJoin('Vmeste\SaasBundle\Entity\Donor', 'd', 'WITH', 'r.donor_id = d.id')
                    ->innerJoin('Vmeste\SaasBundle\Entity\Campaign', 'c', 'WITH', 'r.campaign_id = c.id')
                    ->orderBy('r.id', 'ASC')
                    ->setFirstResult( $offset )
                    ->setMaxResults( self::LIMIT_ROWS );
        $statement = $queryBuilder->execute();
        $this->data = $statement->fetchAll();
        
        /*$query = $this->icpdo->createQuery('SELECT *
                                    FROM Vmeste\SaasBundle\Entity\Recurrent r
                                    INNER JOIN Vmeste\SaasBundle\Entity\Donor d WITH (r.donor_id = d.id)
                                    INNER JOIN Vmeste\SaasBundle\Entity\Campaign c WITH (r.campaign_id = c.id)
                                    WHERE c.status_id = '.$this->status_id_active.'
                                    AND d.status_id = '.$this->status_id_active.'
			                        AND (r.success_date + '.$monthdays.')>='.$today_start.'
			                        AND (r.success_date + '.$monthdays.')<='.$today_end.'
			                        AND r.attempt = '.$attempt.'
			                        ORDER BY r.id ASC LIMIT :offset, :limit;')
            ->setParameter('offset', $offset)
            ->setParameter('limit', self::LIMIT_ROWS);
        $this->data = $query->getResult();*/
    }
}

?>