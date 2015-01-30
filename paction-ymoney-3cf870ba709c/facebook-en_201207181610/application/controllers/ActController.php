<?php

class ActController extends Ymoney_Controller_Action {

    private $records_on_statpage = 10;
    private $action_code_flag = '21354-';
    private $action_code_index = '52934';
    private $action_code_my = '48523';
    private $action_code_create = '23895';
    private $action_code_edit = '48754';
    private $action_code_account = '98347';
    private $action_code_stat = '23903';
    private $maxphotosize = 4500000;
    private $maxphoto = 12;
    private $VK;

    public function init() {
        /* Initialize action controller here */
//        $config = Zend_Registry::get('appConfig');
//        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
//
//        require_once 'vkapi.class.php';
//
//        $this->VK = new vkapi($config->ymoney->APP_ID, $config->ymoney->APP_SECRET, $ymoneyNamespace->vk['api_url']);
//
//        $this->view->appurlorigin = $config->ymoney->APP_PAGE_ORIGIN;
//        $this->view->appdesc = $config->ymoney->description;
//        $this->view->appname = $config->ymoney->APP_NAME;
    }
    
    protected function removeCRLFact($string)
    {
    	$newstring = '';
    	for($i=0;$i<strlen($string);$i++) {
    		if(ord($string{$i})!= 10 && ord($string{$i}) !=13 && in_array($string{$i}, array(0,1,2,3,4,5,6,7,8,9,'-'))) {
    			$newstring .= $string{$i};
    		} else {
    			break;
    		}
    	}    	
    	return $newstring;
    }
    
    protected function removeCRLF($string)
    {
    	$newstring = '';
    	for($i=0;$i<strlen($string);$i++) {
    		if(ord($string{$i})!= 10 && ord($string{$i}) !=13) {
    			$newstring .= $string{$i};
    		} else {
    			break;
    		}
    	}    	
    	return $newstring;
    }
    
    protected function initFB($act)
    {
        $config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $facebook = new Facebook(array(
            'appId'  => $config->ymoney->APP_ID,
            'secret' => $config->ymoney->APP_SECRET
        ));
        $user_id = $facebook->getUser();
        $accesstokenkey = implode('_', array('fb', $facebook->getAppId(), 'access_token'));
        if(isset($_SESSION[$accesstokenkey])) {
            $facebook->setAccessToken($_SESSION[$accesstokenkey]);
        }
        $auth_url = "http://www.facebook.com/dialog/oauth?client_id=" . $config->ymoney->APP_ID . "&client_secret=".
        $config->ymoney->APP_SECRET . "&redirect_uri=" . 
        urlencode($config->ymoney->APP_PAGE) . "&scope=email,publish_stream,user_photos";
        $ymoneyNamespace->fb = $act;
        if($user_id) {	
            try {	
                    $user_profile = $facebook->api('/'.$user_id.'/permissions','GET');	
            if(isset($user_profile['data'][0]['user_photos']) && $user_profile['data'][0]['user_photos'] == 1 && 
            ((strstr($act, $this->action_code_flag.$this->action_code_create) || 
            strstr($act, $this->action_code_flag.$this->action_code_edit)))
                && !strstr($facebook->getAccessToken(), "|")
            ) {
                    $ymoneyNamespace->fb = false;
            } elseif(strstr($act, $this->action_code_flag.$this->action_code_create) || 
            strstr($act, $this->action_code_flag.$this->action_code_edit)) {
                    echo("<script> top.location.href='" . $auth_url . "'</script>");
                            exit;
            } else {
                    $ymoneyNamespace->fb = false;
            }

            } catch(FacebookApiException $e) {
                    echo("<script> top.location.href='" . $auth_url . "'</script>");
                    exit;
            }   
        } else {	
            // No user, print a link for the user to login
            //echo "No user, print a link for the user to login<br>";
            echo("<script> top.location.href='" . $auth_url . "'</script>");
            exit;
        }
    }

    public function indexAction() {
    }
    
    public function loadimageAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');     	
    	$config = Zend_Registry::get('appConfig');
    	$action_id = $this->removeCRLF($this->_getParam('action_id'));
    	$this->initFB($this->action_code_flag.$this->action_code_edit."-".$action_id);
    	$facebook = new Facebook(array('appId'  => $config->ymoney->APP_ID, 'secret' => $config->ymoney->APP_SECRET));
    	$user_id = $facebook->getUser();

        // Выбираем все альбомы пользователя
        $aid = 0;
        $result = $facebook->api('/me/albums');
        if(!empty($result['data'])) {
            foreach($result['data'] as $k=>$v) {
                if($v['name'] == 'Собирайте деньги'){
                    $aid = $v['id'];
                    break;
                }
            }
        }
        // Если альбома нет, то создаем
        if($aid == 0) {
            $album_details = array(
                'message'=> '',
                'name'=> 'Собирайте деньги'
            );
            $create_album = $facebook->api('/me/albums', 'post', $album_details);
            $aid = $create_album['id'];
        }
        $this->view->aid = $aid;
       
    }
    
    public function loadimageajaxAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        require_once APPLICATION_PATH . '/php.php';
    }
}

