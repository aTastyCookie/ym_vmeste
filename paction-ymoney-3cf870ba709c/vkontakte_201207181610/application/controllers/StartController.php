<?php

class StartController extends Ymoney_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    	$config = Zend_Registry::get('appConfig');
        $this->_helper->layout->disableLayout();
        $this->view->appurl = $config->ymoney->APP_PAGE;
    }
}

