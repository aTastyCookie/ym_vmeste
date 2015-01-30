<?php
/**
 *
 * @author andreyg
 * @version 1.0
 */

/**
 * DateFormat Action Helper 
 * 
 * @uses actionHelper Ymoney_Helper
 */
class Ymoney_Helper_DateFormat extends Zend_Controller_Action_Helper_Abstract
{
	private function _month($monthnumber) 
	{
		$Months = array(
			1 => 'января',
			2 => 'февраля',
			3 => 'марта',
			4 => 'апреля',
			5 => 'мая',
			6 => 'июня',
			7 => 'июля',
			8 => 'августа',
			9 => 'сентября',
			10 => 'октября',
			11 => 'ноября',
			12 => 'декабря'
		);
		return $Months[intval($monthnumber)];
	}
	private function _day($daynumber) 
	{
		return $daynumber <= 9 ? intval($daynumber) : $daynumber;
	}
	
    public function direct ($date, $time = null, $totimestamp = null, $onlydate = null)
    {
    	if ($date) {
    		$ADate = explode(" ", $date); 
    		if($totimestamp) {
    			$ADate = explode("-", $ADate[0]);
    			return mktime(0, 0, 0, $ADate[1], $ADate[2], $ADate[0]);
    		} elseif($onlydate) {
    			return $ADate[0];
    		} else {
				if ($time) {
					$ATime = $ADate[1]; 
					$ADate = explode("-", $ADate[0]); 
					$ADate = $this->_day($ADate[2]) . ' ' . $this->_month($ADate[1]);
			        return $ADate." ".$ATime;				
				} else {
					$timestamp = mktime(0, 0, 0, $ADate[1], $ADate[2], $ADate[0]);
					$year = '';
					
					if(($timestamp-time())>378432000) $year = ' '.date('Y', $timestamp);
					$ADate = explode("-", $ADate[0]);
					$ADate = $this->_day($ADate[2]) . ' ' . $this->_month($ADate[1]) . $year;
			        return $ADate;
				}
    		}
    	}
    	else return '';
    }
}
