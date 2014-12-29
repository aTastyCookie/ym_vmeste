<?php
/**
 *
 * @author andreyg
 * @version 1.0
 */

/**
 * NumberFormat Action Helper 
 * 
 * @uses actionHelper Ymoney_Helper
 */
class Ymoney_Helper_NumberFormat extends Zend_Controller_Action_Helper_Abstract
{
    public function direct ($number = 0, $onlynumber = null, $floatnumber = null)
    {
    	if($onlynumber) {
    		return number_format($number, 0, ',', ' ');
    	} elseif($floatnumber) {
    		return number_format($number, 2, ',', ' ');
    	} else {
	    	$number = round($number, 0);
    		if($number>999999999) {
	    		if($number%1000000000>99999999) {
	    			return number_format(($number/1000000000), 1, ',', ' ')." млрд";
	    		} else {
	    			return number_format(($number/1000000000), 0, ',', ' ')." млрд";
	    		}
	    		
	    	} elseif($number>999999) {
	    		if(round($number, -6) == 1000000000) {
	    			return number_format(($number/1000000000), 0, ',', ' ')." млрд";
                        } elseif ($number%1000000 > 99999) {
	    			return number_format(($number/1000000), 1, ',', ' ')." млн";
	    		} else {
	    			return number_format(($number/1000000), 0, ',', ' ')." млн";
	    		}
	    		
	    	} else {
	    		return number_format($number, 0, ',', ' ');
	    	}
    	}
    }
}
