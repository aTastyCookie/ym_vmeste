<?php

class IndexController extends Ymoney_Controller_Action {

    private $action_code_flag = '21354-';
    private $action_code_index = '52934';
    private $action_code_my = '48523';
    private $action_code_create = '23895';
    private $action_code_edit = '48754';
    private $action_code_account = '98347';
    private $action_code_stat = '23903';
    private $action_code_additional = '34621';
    private $action_code_callback = '38432';
    private $config;
    private $wordlength = 24;
    private $usernamelength = 35;
    private $actionnamelength = 94;

    public function init() {
        /* Initialize action controller here */
        $this->config = Zend_Registry::get('appConfig');
    	$this->view->paymenturl = "https://money.yandex.ru/direct-payment.xml";
        $this->view->host = $this->config->ymoney->host;
    }

    protected function initFB($act) {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $facebook = new Facebook(array('appId' => $this->config->ymoney->APP_ID, 'secret' => $this->config->ymoney->APP_SECRET));
        $accesstokenkey = implode('_', array('fb', $facebook->getAppId(), 'access_token'));//echo accesstokenkey; exit;
        if (isset($_SESSION[$accesstokenkey])) {
            $facebook->setAccessToken($_SESSION[$accesstokenkey]);
        }
        $user_id = $facebook->getUser();
        $auth_url = "http://www.facebook.com/dialog/oauth?client_id=" . $this->config->ymoney->APP_ID . "&client_secret=" .
                $this->config->ymoney->APP_SECRET . "&redirect_uri=" .
                urlencode($this->config->ymoney->APP_PAGE) . "&scope=email,publish_stream,user_photos";
        if ($user_id) {
            // We have a user ID, so probably a logged in user.
            // If not, we'll get an exception, which we handle below.
            try {
                $user_profile = $facebook->api('/' . $user_id . '/permissions', 'GET');
            } catch (FacebookApiException $e) {
                // If the user is logged out, you can have a 
                // user ID even though the access token is invalid.
                // In this case, we'll get an exception, so we'll
                // just ask the user to login again here.
                echo("<script> top.location.href='" . $auth_url . "'</script>");
                exit;
            }
        } else {
            // No user, print a link for the user to login
            echo("<script> top.location.href='" . $auth_url . "'</script>");
            exit;
        }
    }

    protected function removeCRLFact($string) {
        $newstring = '';
        for ($i = 0; $i < strlen($string); $i++) {
            if (ord($string{$i}) != 10 && ord($string{$i}) != 13 && in_array($string{$i}, array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, '-'))) {
                $newstring .= $string{$i};
            } else {
                break;
            }
        }
        return $newstring;
    }

    public function sortactions($a) {
        $b = array();
        $keys = array_keys($a);
        shuffle($keys);
        foreach ($keys as $key => $v) {
            $b[] = $a[$v];
        }

        return $b;
    }

    public function indexAction() {
    	
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $facebook = new Facebook(array('appId' => $this->config->ymoney->APP_ID, 'secret' => $this->config->ymoney->APP_SECRET));
        
        if ($ymoneyNamespace->fb) {
            $this->initFB($ymoneyNamespace->fb);
            $actArray = explode('-', $ymoneyNamespace->fb);
            $params = '';
            $act = $actArray[1];
            switch ($act) {
                case $this->action_code_index: $act = 'index';
                    break;
                case $this->action_code_my: $act = 'my';
                    break;
                case $this->action_code_create: $act = 'create';
                    break;
                case $this->action_code_account: $act = 'account';
                    break;
                case $this->action_code_additional: $act = 'additionalpayment'; $this->_redirect($this->_helper->url($act, 'index', 'default'));
                    break;
                case $this->action_code_edit: $act = 'edit';
                    if (isset($actArray[2]))
                        $params = '?action_id=' . $actArray[2]; break;
                case $this->action_code_stat: $act = 'stat';
                    if (isset($actArray[2]))
                        $params = '?action_id=' . $actArray[2]; break;
                default: $act = 'index';
            }
            $ymoneyNamespace->fb = false;
            $this->_redirect($this->_helper->url($act, 'actions', 'default') . $params);
        }

        $signed_request = isset($_REQUEST['signed_request']) ? $_REQUEST['signed_request'] : '';
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);
        $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
        $user_id = false;
        $user_id = $facebook->getUser();
        
        // --- 

        if ($signed_request) {
            $this->view->signed_request = $signed_request;
        } else {
            $this->view->signed_request = $facebook->getSignedRequest();
        }
        // Интерфейс админа
        $this->view->admin = $this->_adminInteface($user_id);
        $this->view->appurl = $this->config->ymoney->APP_PAGE;
        $this->view->apphost = $this->config->ymoney->host;
        // Интерфейс пользователя
        $this->view->user_id = $user_id;
        $this->view->user_name = Application_Model_DbTable_Users::getUserName($user_id); 
        $this->view->cancreate = $this->cancreate($user_id);
        $this->view->photos = array();
        
        if ($this->_getParam('app_data') || isset($data['app_data'])) {
            if ($this->_getParam('app_data')) {
                $appdata = $this->removeCRLFact($this->_getParam('app_data'));

                if (strstr($appdata, $this->action_code_flag)) {
                    $actArray = explode('-', $appdata);
                    $params = '';
                    $act = $actArray[1];
                    switch ($act) {
                        case $this->action_code_index: $act = 'index';
                            break;
                        case $this->action_code_my: $act = 'my';
                            break;
                        case $this->action_code_create: $act = 'create';
                            break;
                        case $this->action_code_account: $act = 'account';
                            break;
		                case $this->action_code_additional: $act = 'additionalpayment'; $this->_redirect($this->_helper->url($act, 'index', 'default'));
		                    break;
                        case $this->action_code_edit: $act = 'edit';
                            if (isset($actArray[2]))
                                $params = '?action_id=' . $actArray[2]; break;
                        case $this->action_code_stat: $act = 'stat';
                            if (isset($actArray[2]))
                                $params = '?action_id=' . $actArray[2]; break;
                        default: $act = 'index';
                    }
                    $this->_redirect($this->_helper->url($act, 'actions', 'default') . $params);
                } else $this->viewac($appdata, $facebook);
            } else $this->viewac($data['app_data'], $facebook);
        } else {

            // Главная страница приложения
        	$this->view->first_time = $this->_getFirsttime($user_id);
            $actionsModel = new Application_Model_DbTable_Action();
            $actions = $actionsModel->getActions(null, true);
            $actions = array_merge($actions, $actionsModel->getActions(null, null, null, null, null, null, null, true, 5));
            $actions = $this->sortactions($actions);

            $this->view->hasactions = false;
            if ($user_id) {
                $hasacarr = $actionsModel->getActions($user_id);
                if (count($hasacarr) > 0) {
                    $this->view->hasactions = true;
                }
            }

            $photoModel = new Application_Model_DbTable_Photo();
            foreach ($actions as $k => $action) {
                
                $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
                //Если акция завершена - меняем статус
                if ($action['date_end'] && $action['date_end'] != '0000-00-00 00:00:00' && $end_stamp < mktime()) {
                    if ($action['completed'] == 'N') {
                        $ac = $actionsModel->find($action['id'])->current();
                        $ac->completed = 'Y';
                        $ac->save();
                        unset($ac);
                        unset($actions[$k]);
                    }
                    continue;
                }

                $this->_actionProcess($actions[$k], $photoModel, $facebook);
                $this->_changeActionName($actions[$k]);
            }
            
            
            $this->view->actions = $actions;
            $this->view->widthkoef = $this->config->ymoney->widthkoef;
            $ymoneyNamespace->fb = false;
            $script = "var fundAjaxUrl = '{$this->_helper->url('fundraisingstatsajax', 'actions', 'default')}';\n";
            $this->view->headScript()->prependScript($script, $type = 'text/javascript');
            $this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
            $this->view->headMeta()->setProperty('og:title', $this->config->ymoney->APP_NAME);
            $this->view->headMeta()->setProperty('og:type', "website");
            $this->view->headMeta()->setProperty('og:url', $this->config->ymoney->APP_PAGE);
            $this->view->headMeta()->setProperty('fb:app_id', $this->config->ymoney->APP_ID);
            $this->view->headMeta()->setProperty('og:image', $this->config->ymoney->host . '/i/app_75.png');
            $this->view->headMeta()->setProperty('og:description', $this->config->ymoney->description);
        }
    }

    public function additionalpaymentAction()
    {
    	$ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
    	$facebook = new Facebook(array('appId' => $this->config->ymoney->APP_ID, 'secret' => $this->config->ymoney->APP_SECRET));
    	$signed_request = isset($_REQUEST['signed_request']) ? $_REQUEST['signed_request'] : '';
    	list($encoded_sig, $payload) = explode('.', $signed_request, 2);
    	$data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
    	$user_id = false;
    	$user_id = $facebook->getUser();
    	if ($signed_request) {
    		$this->view->signed_request = $signed_request;
    	} else {
    		$this->view->signed_request = $facebook->getSignedRequest();
    	}
    	// Интерфейс админа
    	$this->view->admin = $this->_adminInteface($user_id);
    	$this->view->appurl = $this->config->ymoney->APP_PAGE;
    	$this->view->apphost = $this->config->ymoney->host;
    	// Интерфейс пользователя
    	$this->view->user_id = $user_id;
    	$this->view->user_name = Application_Model_DbTable_Users::getUserName($user_id);
    	$this->view->cancreate = $this->cancreate($user_id);
    	
    	$this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
    	$this->view->headMeta()->setProperty('og:title', $this->config->ymoney->APP_NAME);
    	$this->view->headMeta()->setProperty('og:type', "website");
    	$this->view->headMeta()->setProperty('og:url', $this->config->ymoney->APP_PAGE);
    	$this->view->headMeta()->setProperty('fb:app_id', $this->config->ymoney->APP_ID);
    	$this->view->headMeta()->setProperty('og:image', $this->config->ymoney->host . '/i/app_75.png');
    	$this->view->headMeta()->setProperty('og:description', $this->config->ymoney->description);
    }
    
    public function cancreate($user_id) {
        $actionsModel = new Application_Model_DbTable_Action();
        $admin = $this->_adminInteface($user_id);
        if ($user_id && !$admin) {
            $actions = $actionsModel->getActions($user_id, null, null, null, null, null, null, null, null, null, true);
            $i = 0;
            foreach ($actions as $action) {
                if ($this->_helper->dateFormat($action['date_start'], null, true) >= mktime(0, 0, 0, date('m'), date('d'), date('Y'))) {
                    $i++;
                }
                if ($i == 5) {
                    return false;
                    break;
                }
            }
        }
        return true;
    }

    public function viewac($id, $facebook) {
        $user_id = $facebook->getUser();
        $id = intval($id);
        
        //$this->view->user_avatar = "http://www.facebook.com/profile.php?id=" . $user_id;
        if ($id > 0) {
            $script = "var stopAjaxUrl = '{$this->_helper->url('stop', 'actions', 'default')}';\n";
            $script .= "var startAjaxUrl = '{$this->_helper->url('start', 'actions', 'default')}';\n";
            $script .= "var fundAjaxUrl = '{$this->_helper->url('fundraisingstatsajax', 'actions', 'default')}';\n";

            if ($this->view->admin) {
                $script .= "var topAjaxUrl = '{$this->_helper->url('top', 'actions', 'default')}';\n";
                $script .= "var nomainAjaxUrl = '{$this->_helper->url('nomain', 'actions', 'default')}';\n";
                $script .= "var onmainAjaxUrl = '{$this->_helper->url('onmain', 'actions', 'default')}';\n";
                $script .= "var hideAjaxUrl = '{$this->_helper->url('hide', 'actions', 'default')}';\n";
                $script .= "var blockAjaxUrl = '{$this->_helper->url('block', 'actions', 'default')}';\n";
                $script .= "var untopAjaxUrl = '{$this->_helper->url('untop', 'actions', 'default')}';\n";
                $script .= "var unhideAjaxUrl = '{$this->_helper->url('unhide', 'actions', 'default')}';\n";
                $script .= "var unblockAjaxUrl = '{$this->_helper->url('unblock', 'actions', 'default')}';\n";
            }
            $this->view->headScript()->prependScript($script, $type = 'text/javascript');

            $actionModel = new Application_Model_DbTable_Action();
            $action = $actionModel->getAction($id);
            $this->view->actionforajax = $action;
            unset($this->view->actionforajax['description']);

            if (isset($action['id'])) {
            	$this->view->user_profile_picture = Application_Model_DbTable_Users::get_user_profile_picture($facebook, $action['user_id']);
            	
                $action['left'] = null;
                $this->view->error = null;
                if ($this->view->user_id && ($action['blocked'] == 'Y' || $action['draft'] == 'Y') && $action['user_id'] == $this->view->user_id) {
                    if ($action['blocked'] == 'Y' && $action['draft'] == 'N')
                        $this->view->error = 'акция заблокирована администратором';
                    if ($action['blocked'] == 'N' && $action['draft'] == 'Y')
                        $this->view->error = 'акция сохранена как черновик и не опубликована';
                    if ($action['blocked'] == 'Y' && $action['draft'] == 'Y')
                        $this->view->error = 'акция заблокирована администратором, сохранена как черновик и не опубликована';
                } elseif (($action['blocked'] == 'Y' || $action['draft'] == 'Y') && !$this->view->admin) {
                    $this->_redirect($this->_helper->url('index', 'actions', 'default') . '?no_action=1');
                }

                //$start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
                $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);

                if ($action['url'] != 'http://' && strlen($action['url'])) {
                    if (!strstr($action['url'], "http://")) $action['url'] = 'http://' . $action['url'];
                } else  $action['url'] = '';

                if ($action['completed'] == 'Y') $action['left'] = 'акция завершена';
                if ($action['date_end'] && $action['date_end'] != '0000-00-00 00:00:00' && $end_stamp < mktime()) {
                    if ($action['completed'] == 'N') {
                        $ac = $actionModel->find($action['id'])->current();
                        $ac->completed = 'Y';
                        $ac->save();
                        unset($ac);
                    }
                    $action['left'] = 'акция завершена';
                }
                $this->_actionProcess($action);

                if ($action['group'] == 1) {
                    $usersactionModel = new Application_Model_DbTable_Usersaction();
                    $friendlist = $usersactionModel->selectfriends($action['id']);
                    $action['friends'] = $friendlist;
                    $action['required_sum_group'] = $action['required_sum'] / (count($action['friends']) + 1) * 1.005;
                    $this->view->redirect_friends_url = 'https://money.yandex.ru/direct-payment.xml?receiver=' .
                            $action['receiver'] . '&Formcomment=' . urlencode("На акцию в Facebook «" . $action['name'] . "»") . '&destination=' .
                            urlencode('Для акции «'.$action['name'].'» от пользователя facebook.com/' . $user_id . '. Чтобы отправить деньги анонимно, удалите весь комментарий.') .
                            '&sum=' . ($action['required_sum_group']) . '&_openstat=socapp;fb;p2p;list&label=fb_' . $action['id'];
                } else {
                    $action['required_sum_group'] = 0;
                }

                if ($action['left'] == 'акция завершена') {
                    $this->view->error = 'Сбор денег по этой акции уже закончен, но вы можете поддержать другое доброе дело.';
                }

                $action['description'] = nl2br($action['description']);

                $action['short'] = $this->_shortDescription($action['description']);
                
                $this->view->widthkoef = $this->config->ymoney->bigwidthkoef;
                $this->view->action = $action;
                
                $tmpName = explode(' ', $this->view->action['name']);
                $i = 0;
                $s = count($tmpName);
                $tmp1 = '';
                while($i <= $s) {
                    if(isset($tmpName[$i]) && mb_strlen($tmpName[$i], 'UTF-8') > 44) {
                        $tmpName[$i] = mb_substr($tmpName[$i], 0, 44, 'UTF-8');
                        array_splice($tmpName, ++$i, $s-$i);
                        $tmp1 = '...';
                        break;
                    }
                    
                    $i++;
                }
                $this->view->action['filteredName'] = implode(' ', $tmpName) . $tmp1;
                
                $photoModel = new Application_Model_DbTable_Photo();
                $this->view->selectedphotos = array();

                $this->view->photo = '';
                $ar = $photoModel->getFullPhotosByAction($action['id'], 1);
                foreach($ar as $k=>$v) {
                	$url = $v['src_big'];
                	$Headers = @get_headers($url);
                	if(strpos($Headers[0], '200')) {
                		$this->view->photo = $v['src_big'];
                		break;
                	} else {
                		$where = $photoModel->getAdapter()->quoteInto('pid = ?', $v['pid']);
                		$photoModel->delete($where);
                	}
                }
                //var_dump($this->view->photo); exit;
                
                $shelpusers = new Application_Model_DbTable_Shelpusers();
                $social_users = $shelpusers->selectUsers($action['id']);
                $this->view->social_users = array();
                foreach($social_users as $u) {
                    if($u['user_id'] != "") {
                        $tmp = $facebook->api('/' . $u['user_id'] . '/', 'GET');
                        $this->view->social_users[] = $tmp;
                    }
                }
                if(count($this->view->social_users) > 0) {
                    $this->view->social_string = $this->_pluralForm(count($this->view->social_users), "Рассказал ", "Рассказали ", "Рассказали ")
                            . count($this->view->social_users) . $this->_pluralForm(count($this->view->social_users), " человек", " человека", " человек");
                }

                $this->view->share = $this->config->ymoney->APP_PAGE . '?app_data=' . $action['id'] . '&t=' . urlencode($action['name']);
                $this->view->redirect_url = $this->config->ymoney->APP_PAGE . '?app_data=' . $action['id'];
                $this->view->appPicture = $this->config->ymoney->host . '/i/app_75_new.png';

                $this->view->headTitle($action['name']);
                $this->view->headMeta(strip_tags($action['description']), 'description');

                $this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
                $this->view->headMeta()->setProperty('og:title', $action['name']);
                $this->view->headMeta()->setProperty('og:type', "website");
                $this->view->headMeta()->setProperty('og:url', $this->view->redirect_url);
                $this->view->headMeta()->setProperty('fb:app_id', $this->config->ymoney->APP_ID);
                $this->view->headMeta()->setProperty('og:image', $this->config->ymoney->host . '/i/app_75.png');
                $this->view->headMeta()->setProperty('og:description', strip_tags($action['description']));
                
                $this->view->paymentsum = ((isset($action['required_sum_group']) && $action['required_sum_group']>0) ? $this->action['required_sum_group'] : 100 ) ;
                
                $this->_getSimilarActions($action, $actionModel, $photoModel, $facebook);
                
            } else {
                $this->_redirect($this->_helper->url('index', 'actions', 'default') . '?no_action=1');
            }
        }
    }

    private function uni_strsplit($string, $split_length = 1) {
        preg_match_all('`.`u', $string, $arr);
        $arr = array_chunk($arr[0], $split_length);
        $arr = array_map('implode', $arr);
        return $arr;
    }

    public function requestforpaymentAction() {
        /* $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
          if(!isset($ymoneyNamespace->access_token)) {
          $this->_redirect($this->_helper->url('index', 'index', 'default'));
          }
          $this->config = Zend_Registry::get('appConfig');
          ZenYandexClient::setClientId($this->config->ymoney->client_id);
          $access_token = $ymoneyNamespace->access_token;
          $zayac = new ZenYandexClient($access_token);
          $rubles = floatval($this->_getParam('rubles') + 0);
          if($rubles<0.02) $rubles = 0.02;
          $responsePayment = $zayac::requestPayment($rubles);
          if($responsePayment['status'] == 'success') {
          $this->view->responsePayment = $responsePayment;
          $request_id = $responsePayment['request_id'];
          $processPayment = $zayac::processPayment($request_id);

          if($processPayment['status'] != 'refused') {
          $this->view->processPayment = $processPayment;
          } else {
          $this->view->processPayment = $processPayment['error'];
          }
          } else {
          $this->view->responsePayment = $responsePayment['error'];
          } */
    }

    public function redirectAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if ($this->_getParam('action_id') && intval($this->_getParam('action_id')) > 0) {
            $this->_redirect($this->config->ymoney->APP_PAGE_PAGE . '&app_data=' . intval($this->_getParam('action_id')) . '&ref=nf');
        } else {
            $this->_redirect($this->_helper->url('index', 'index', 'default'));
        }
    }

    private function _adminInteface($user_id) {
        if ($user_id) {
            $userModel = new Application_Model_DbTable_Users();
            $user = $userModel->find($user_id)->current();
            if ($user && $user->admin == 'Y') {
                return true;
            }
        }
        return false;
    }
    
    private function _pluralForm($n, $form1, $form2, $form5) {
        $n = abs($n) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20) return $form5;
        if ($n1 > 1 && $n1 < 5) return $form2;
        if ($n1 == 1) return $form1;
        return $form5;
    }
    
    private function _descriptionCut($text) {
        $res = $text;
        if(strlen($text) > 300) {
            if(strpos($text, "\r\n") > 0) {
                $res = str_replace('/r', '', substr($text, 0, strpos($text, "\n")-1)) . '...';
            } else {
                $res = substr($text, 0, 300) . '...';
            }
        } elseif(strpos($text, "\n") > 0) {
            $res = str_replace('/r', '', substr($text, 0, strpos($text, "\n")-1)) . '...';
        }
        
        return $res;
    }
    
    private function _actionProcess(&$action, $photoModel = null, $facebook = null) {
        
        $start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
        $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);

        if (!$action['date_end'] || $action['date_end'] == '0000-00-00 00:00:00') {
            $action['dates'] = 'всегда';
        } elseif ($action['date_end'] != '0000-00-00 00:00:00') {
            $action['dates'] = 'до ' . $this->_helper->dateFormat($action['date_end']);

            if ($action['completed'] == 'Y') {
                $action['left'] = 'акция завершена';
            } 
        }
        
        if (strlen($action['name']) == 0) $action['name'] = 'Без названия';
        if ($action['required_sum'] > 0 && $action['required_sum'] > ($action['current_sum'] + $action['all_sum'])) {
        	$action['percents'] = round((($action['current_sum'] + $action['all_sum']) / $action['required_sum']) * 100);
        	if ($action['percents'] < 1 && ($action['current_sum'] + $action['all_sum']) > 0) $action['percents'] = 1;
        } elseif ($action['required_sum'] > 0 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum']))  $action['percents'] = 100;
        else $action['percents'] = false;
        
        if ($action['required_sum'] > 0)  $action['required_sumF'] = $this->_helper->numberFormat($action['required_sum']);
        else $action['required_sumF'] = 0;
        if ($action['current_sum'] > 0 || $action['all_sum'] > 0) {
        	$action['current_sumF'] = $this->_helper->numberFormat(($action['current_sum'] + $action['all_sum']));
        	if (($action['current_sum'] + $action['all_sum']) < 1000000 && $action['required_sum'] >= 1000000)
        		$action['current_sum_suffix'] = ' руб. ';
        	else $action['current_sum_suffix'] = '';
        } else $action['current_sumF'] = 0;
        
        if($photoModel && $facebook) {
        	$ar = $photoModel->getFullPhotosByAction($action['id'], 1);
        	foreach($ar as $k=>$v) {
        		$url = $v['src'];
        		$Headers = @get_headers($url);
        		if(strpos($Headers[0], '200')) {
        			$this->view->photos[$action['id']] = $v['src'];
        		} else {
        			$where = $photoModel->getAdapter()->quoteInto('pid = ?', $v['pid']);
        			$photoModel->delete($where);
        		}
        	}
        	$action['paymentsum'] = 100;
        	//var_dump($this->view->photos);
        }
    }

    
    private function _getFirsttime($user_id)
    {
    	$defaultNamespace = new Zend_Session_Namespace('Default');
    	$user = false;
    	if($user_id) {
    		$userModel = new Application_Model_DbTable_Users();
    		$user = $userModel->getUser($user_id);
    	}
    	if (!isset($defaultNamespace->firsttime) && !$user) {
    		$defaultNamespace->setExpirationSeconds(31536000);
    		$defaultNamespace->firsttime = 1; // first time
    		return true;
    	} else {
    		return false;
    	}
    }

	private function _changeActionName(&$action)
	{
		$tmpName = explode(' ', $action['name']);
		$i = 0;
		$s = count($tmpName);
		$tmp1 = "";
		while($i < $s) {
			if(isset($tmpName[$i]) && mb_strlen($tmpName[$i], 'UTF-8') > $this->wordlength) {
				$tmpName[$i] = mb_substr($tmpName[$i], 0, $this->wordlength, 'UTF-8');
				array_splice($tmpName, ++$i, $s-$i);
				$tmp1 = '...';
				break;
			}
			$i++;
		}
		$action['filteredName'] = implode(' ', $tmpName) . $tmp1;
		$action['filteredName'] = mb_strlen($action['filteredName'], "UTF-8") <= $this->actionnamelength ? $action['filteredName'] 
		: mb_substr($action['filteredName'], 0, ($this->actionnamelength-1), "UTF-8") . "…";
		
		// убираем описание, которое может конфликтовать с json
		$action['description'] = $this->_descriptionCut($action['description']);
		
		$action['Username'] = mb_strlen($action['Username'], "UTF-8") <= $this->usernamelength ? $action['Username'] :
		mb_substr($action['Firstname'], 0, 1, "UTF-8") . '. ' . $action['Lastname'];
	}

	private function _shortDescription($string)
	{
		$string = str_replace('<br />', ' ', $string);
		$string = $this->uni_strsplit($string);
		$short = '';
		foreach ($string as $sym) {
			if (ord($sym) != 10 && ord($sym) != 13) $short .= $sym;
		}
		
		return addslashes($short);
	}

	private function _getSimilarActions($action, $actionModel, $photoModel, $facebook, $limit = 3)
	{
		$result = $actions_ids = array();
		$rubricsModel = new Application_Model_DbTable_ActionRubric();
		$rubrics = $rubricsModel->getRubrics($action['id']);
		$k = 0;
		if(strlen($rubrics)>0){
			$actions_ids = $rubricsModel->getActions($rubrics, $action['id'], $limit);
			$k = count($actions_ids);
			if($k>0){
				for($i=0;$i<$k;$i++) {
					$action = $actionModel->getAction($actions_ids[$i]);
					$this->_actionProcess($action, $photoModel, $facebook);
					$this->_changeActionName($action);
					if($action) $result[] = $action;
				}
			}
		}
		
		$this->view->similar_actions = $result;
	}
}

