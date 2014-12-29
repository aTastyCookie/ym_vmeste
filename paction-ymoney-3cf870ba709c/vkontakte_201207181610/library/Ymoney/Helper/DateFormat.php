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
			1 => 'янв',
			2 => 'фев',
			3 => 'мар',
			4 => 'апр',
			5 => 'май',
			6 => 'июн',
			7 => 'июл',
			8 => 'авг',
			9 => 'сен',
			10 => 'окт',
			11 => 'ноя',
			12 => 'дек'
		);
		//echo $Months[intval($monthnumber)]." ";
		return $Months[intval($monthnumber)];
	}
	private function _day($daynumber) 
	{
		return $daynumber <= 9 ? intval($daynumber) : $daynumber;
	}
	
    public function direct ($date, $time = null, $totimestamp = null, $onlydate = null)
    {
    	if ($date)
    	{
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
				}
				else {
					$ADate = explode("-", $ADate[0]);
					$ADate = $this->_day($ADate[2]) . ' ' . $this->_month($ADate[1]);
			        return $ADate;
				}
    		}
    	}
    	else return '';
    }
}
