<?php
/**
 *
 * @author andreyg
 * @version 1.0
 */

/**
 * HumanDate Action Helper 
 * 
 * @uses actionHelper Ymoney_Helper
 */
class Ymoney_Helper_HumanDate extends Zend_Controller_Action_Helper_Abstract
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
	
    public function direct ($timestamp)
    {
    	if ($timestamp)
    	{
    		$Date = date('j', $timestamp).' '.$this->_month(date('n', $timestamp));		
    		return $Date;
    	}
    	else return '';
    }
}
