<?php
namespace Vmeste\SaasBundle\Util;
use Vmeste\SaasBundle\Entity\Recurrent;
use Vmeste\SaasBundle\Entity\Transaction;
use Vmeste\SaasBundle\Entity\Donor;
use Vmeste\SaasBundle\Entity\Campaign;

class Recurrent {
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
	private $email_subject_0;
	private $url_unsubcribe;
	private $url_subcribe;
	public 	$recurrent;
	public 	$data;
	private $stmt;
	
	public function __construct($params = array()) {
		if(!empty($params)) 
			foreach($params as $key=>$param)
				$this->$key = $param;
		else return false;
		
		$this->email_headers = 'MIME-Version: 1.0' . "\r\n"
								. 'Content-type: text/html; charset=utf-8' . "\r\n"
								. 'From: Автоплатежи <birthday@example.com>' . "\r\n";
// CHANGE IT -> move into params when initialization
		$this->url_unsubcribe = 'http://test.test.ru';
		$this->url_subcribe = 'http://test.test.ru2';
		
		$this->recurrent = new stdClass;
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
	
	private function send_money($recur)
	{
		$attempt = 0;
		$orderId = (int)($recur['clientOrderId'] + 1);
        $output_array = array('clientOrderId'=>$orderId, 
        						'invoiceId'=>$recur['invoiceId'],
        						'amount'=>$recur['amount'],
        						'orderNumber'=>$recur['campaign_id'] . '-' .$orderId);
        if($recur['cvv']) $output_array['cvv'] = $recur['cvv'];	
        					
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
        $xml = new DOMDocument();
		$xml->loadXML($result);
		$result = array();
		$responses = $xml->getElementsByTagName('repeatCardPaymentResponse');
		foreach($responses as $response) {
			$result['error'] = $response->getAttribute('error');
			$result['status'] = $response->getAttribute('status');
			$result['techMessage'] = $response->getAttribute('techMessage');
		}
		
		$donor = $this->icpdo->getRepository('Vmeste\SaasBundle\Entity\Donor')->findOneBy(array('id' => $recur['donor_id']));
		
    	if(!$donor) {
			return false;
		}
		
		if($result['error'] == 0) {
			// Insert transaction
// !!!!!			
			$mc_currency = "RUB";
			$payment_status = "Completed";
			$transaction_type = "donate";
			
			$transaction = new Transaction();
			$transaction->setDates();
			$transaction->setCurrency($mc_currency);
			$transaction->setDetails(mysql_real_escape_string(json_encode($result)));
			$transaction->setDonor($recur['donor_id']);
			$transaction->setGross(floatval($recur['amount']));
			$transaction->setPaymentStatus($payment_status);
			$transaction->setTransactionType($transaction_type);
			//$transaction->setUser()

			$attempt = 0;
			$success_date = time();
		} else {
			if($recur['attempt'] == 3) {
				$this->notify_about_auto_deleting($donor->getEmail());
// CHANGE IT
				$this->icpdo->query("DELETE FROM ".$this->icpdo->prefix."recurrents WHERE id = ".$recur['id'].";");
				$this->icpdo->query("UPDATE ".$this->icpdo->prefix."donors 
									SET deleted = 1
									WHERE id = ".$recur['donor_id'].";");
				$attempt = 4;
			} else {
				$attempt = $recur['attempt'] + 1;
				$success_date = $recur['success_date'];
			}
			
		}
		
		if($attempt<4) {
// CHANGE IT
			$this->icpdo->query("UPDATE ".$this->icpdo->prefix."recurrents 
					SET clientOrderId = '$orderId',
						orderNumber = '".$recur['campaign_id'] . '-' .$orderId."',
						last_operation_time = '".time()."',
						last_status = '".$result['status']."',
						last_error = '".$result['error']."',
						last_techMessage = '".$result['techMessage']."',
						attempt = '".$attempt."',
						success_date = '".$success_date."'
					WHERE id = ".$recur['id']);
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
	
	public function notify_about_subscription()
	{
// CHANGE IT
		$template = file_get_contents(__DIR__.'/email_templates/'.self::TEMPLATE_SUBSCRIBED);
		$body = str_replace('{param1}', $this->recurrent->title, $template);
		$body = str_replace('{param2}', $this->url_unsubcribe, $body);
		@mail($this->recurrent->email, self::EMAIL_SUBJECT_3, $body, $this->headers);
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