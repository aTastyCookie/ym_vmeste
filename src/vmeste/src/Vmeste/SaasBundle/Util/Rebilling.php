<?php
namespace Vmeste\SaasBundle\Util;
use Vmeste\SaasBundle\Entity\Recurrent;
use Vmeste\SaasBundle\Entity\Transaction;
use Vmeste\SaasBundle\Entity\Donor;
use Vmeste\SaasBundle\Entity\Campaign;

class Rebilling {
	const SSL_VERIFYPEER = false;
	const SSL_VERIFYHOST = false;
	const USERAGENT = 'Ymoney CollectMoney';
	const CONNECTTIMEOUT = 30;
	const HTTPHEADER = 'application/x-www-form-urlencoded';
	const NO_ERROR = 0;
	const LIMIT_ROWS = 100;
	const MONTH = 2592000;
	const MONTH33 = 2851200;
	const MONTH36 = 3110400;
	const MONTH39 = 3369600;
	const DAYS_BEFORE = 259200;
	const TEMPLATE_NOTIFY = 'template_0.php';
	const EMAIL_SUBJECT_0 = 'Подтверждение автоплатежа [Собирайте Деньги]';
	const TEMPLATE_THANKS = 'template_1.php';
	const EMAIL_SUBJECT_1 = 'Спасибо!';
	const TEMPLATE_DELETED = 'template_2.php';
	const EMAIL_SUBJECT_2 = 'Автоплатеж удален';
	const TEMPLATE_SUBSCRIBED = 'template_3.php';
	const EMAIL_SUBJECT_3 = 'Вы подписаны на автоплатежи';
	
	private $url;
	private $path_to_cert;
	private $path_to_key;
	private $cert_pass;
	private $icpdo;
	private $email_headers;
	private $url_unsubcribe;
	private $url_subcribe;
	public 	$recurrent;
	public 	$data;
	private $stmt;
    private $status_blocked;
	
	public function __construct($params = array()) {
		if(!empty($params)) 
			foreach($params as $key=>$param)
				$this->$key = $param;
		else return false;
		
		$this->email_headers = 'MIME-Version: 1.0' . "\r\n"
								. 'Content-type: text/html; charset=utf-8' . "\r\n"
								. 'From: Автоплатежи <birthday@example.com>' . "\r\n";
		
		$this->recurrent = new \stdClass;
        $this->status_blocked = $this->icpdo
                                    ->getRepository('Vmeste\SaasBundle\Entity\Status')
                                    ->findOneBy(array('status' => 'BLOCKED'));
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
		$attempt = 0;
        $orderId = time();
        $orderNumber = $recur->getCampaignId() . '-' . $orderId . rand(1, 1000);
        $output_array = array('clientOrderId'=>$orderId,
        						'invoiceId'=>$recur->getInvoiceId(),
        						'amount'=>$recur->getAmount(),
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
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Ymoney Vmeste');
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
		
		if($result['error'] == 0) {
			// Insert transaction
			$transaction = new Transaction();
			$transaction->setDates();
			$transaction->setCurrency("RUB");
			$transaction->setDetails(mysql_real_escape_string(json_encode($result)));
			$transaction->setDonor($donor);
			$transaction->setGross(floatval($recur['amount']));
			$transaction->setPaymentStatus("Completed");
            $transaction->setTransactionType("YMKassa: donate");
            $transaction->setCampaign($campaign);
            $transaction->setInvoiceId($recur['invoiceId']);
            $this->icpdo->persist($transaction);
            $this->icpdo->flush();

			$attempt = 0;
			$success_date = time();
		} else {
			if($recur->getAttempt() == 3) {
				$this->notify_about_auto_deleting($donor->getEmail());
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
// CHANGE IT
		$sql = "SELECT r.*, c.title, d.email FROM udb_recurrents r 
			INNER JOIN udb_campaigns c on c.id = r.campaign_id
			INNER JOIN udb_donors d on d.id = r.donor_id
			WHERE c.status = 1 AND c.deleted = 0 AND d.deleted = 0 AND d.status = 1
			AND (r.success_date + $monthdays)>=$today_start
			AND (r.success_date + $monthdays)<=$today_end
			AND r.attempt = $attempt
			ORDER BY r.id ASC LIMIT :offset, :limit;";
		//echo $sql;
		$this->stmt = $this->icpdo->link->prepare($sql);
		$this->stmt->bindValue(':offset', 0, PDO::PARAM_INT);
		$this->stmt->bindValue(':limit', self::LIMIT_ROWS, PDO::PARAM_INT);
		$this->stmt->execute();
		$this->data = $this->stmt->fetchAll();
		
		while(!empty($this->data)){
			foreach($this->data as $recur) {
				$this->send_money($recur);
			}
		}
	}
	
	private function attempt_notify($attempt, $today_start, $today_end, $monthdays)
	{
// CHANGE IT
		$sql = "SELECT r.*, c.title, d.email FROM udb_recurrents r 
			INNER JOIN udb_campaigns c on c.id = r.campaign_id
			INNER JOIN udb_donors d on d.id = r.donor_id
			WHERE c.status = 1 AND c.deleted = 0 AND d.deleted = 0 AND d.status = 1
			AND (r.success_date + $monthdays - ".self::DAYS_BEFORE.")>=$today_start
			AND (r.success_date + $monthdays - ".self::DAYS_BEFORE.")<=$today_end
			AND r.attempt = $attempt
			ORDER BY r.id ASC LIMIT :offset, :limit;";
		//echo $sql;
		$this->stmt = $this->icpdo->link->prepare($sql);
		$this->stmt->bindValue(':offset', 0, PDO::PARAM_INT);
		$this->stmt->bindValue(':limit', self::LIMIT_ROWS, PDO::PARAM_INT);
		$this->stmt->execute();
		$this->data = $this->stmt->fetchAll();
		//print_r($data);
		$this->notify_about_payment();
	}
	
	public function notify_about_subscription( $context )
	{

        $message = \Swift_Message::newInstance()
        	->setSubject('Спасибо за помощь!')
	        ->setFrom($context->container->getParameter('pass.recover.email.from'))
	        ->setTo($this->recurrent->email)
	        ->setBody(
                $context->renderView(
	                'VmesteSaasBundle:Email:successfullSubscription.html.twig',
	                array(
	                    'sum' => $this->recurrent->sum,
	                    'unsubscribe_url' => $this->url_unsubcribe . 
	                    					'?recurrent=' . $this->recurrent->id . 
	                    					'&invoice=' . $this->recurrent->invoice,
	                    'fond' => $this->recurrent->fond)
	            )
	        );

        $context->get('mailer')->send($message);
	}
	
	private function notify_about_auto_deleting($email)
	{
		$offset = 0;
// CHANGE IT
		$template = file_get_contents(__DIR__.'/email_templates/'.self::TEMPLATE_DELETED);
		
		$body = str_replace('{param1}', $this->url_subcribe, $template);
		
		//echo $body;
		@mail($email, self::EMAIL_SUBJECT_2, $body, $this->headers);	
	}
	
	private function notify_about_success_payment($email)
	{
		$offset = 0;
// CHANGE IT
		$template = file_get_contents(__DIR__.'/email_templates/'.self::TEMPLATE_THANKS);
		
		$body = str_replace('{param1}', '', $template);
		
		//echo $body;
		@mail($email, self::EMAIL_SUBJECT_1, $body, $this->headers);	
	}
	
	private function notify_about_payment()
	{
		$offset = 0;
// CHANGE IT
		$template = file_get_contents(__DIR__.'/email_templates/'.self::TEMPLATE_NOTIFY);
		
		while(!empty($this->data)){
			foreach($this->data as $recur) {
				if(!empty($recur['email'])){
					// TODO: template
					$body = str_replace('{param1}', $recur['amount'], $template);
					$body = str_replace('{param2}', $recur['title'], $body);
					$body = str_replace('{param3}', $this->url_unsubcribe, $body);
					
					//echo $body;
					@mail($recur['email'], self::EMAIL_SUBJECT_0, $body, $this->headers);				
				} else continue;
			}
			$offset += self::LIMIT_ROWS;
			$this->data = $this->_next_data($this->stmt, $offset);
		}
	}
	
	private function _next_data($stmt, $offset)
	{
// CHANGE IT
		$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
		$stmt->bindValue(':limit', self::LIMIT_ROWS, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}
}

?>