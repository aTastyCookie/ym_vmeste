<?php

/**
 * DataController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class DataController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
		header("HTTP/1.0 404 Not Found");
	}

}
