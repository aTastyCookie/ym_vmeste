<?php

class Ymoney_Controller_Plugin_VkSession extends Zend_Controller_Plugin_Abstract {

  public function preDispatch(Zend_Controller_Request_Abstract $request) {
    $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
    $array = array();
    foreach(array(

      'api_url',
      'api_id',
      'api_settings',
      'viewer_id',
      'viewer_type',
      'sid',
      'secret',
      'access_token',
      'user_id',
      'group_id',
      'is_app_user',
      'auth_key',
      'language',
      'parent_language',
      'ad_info',
      'referrer',
      'lc_name'
    ) as $key) {
      if($key=='api_id' || $key=='viewer_id' || $key=='user_id' || $key=='group_id') $array[$key] = (int)$request->getParam($key);     
      else {
        $array[$key] = str_replace(array('"', "'"), '', $request->getParam($key));
      }
    }
//d($array);
    if($array['api_url']&&$array['access_token']) {
      $ymoneyNamespace->vk = $array;
      Zend_Registry::set('vk', $array);
      //d(Zend_Registry::get('vk'));
    }
	//d(Zend_Registry::get('vk'));
  }
}
