<?php

class Ymoney_Controller_Plugin_UriHash extends Zend_Controller_Plugin_Abstract {

  public function preDispatch(Zend_Controller_Request_Abstract $request) {

    $request->setParam('app_data',  str_replace('app_data=', '', $request->getParam('hash', '')));
  }
}
