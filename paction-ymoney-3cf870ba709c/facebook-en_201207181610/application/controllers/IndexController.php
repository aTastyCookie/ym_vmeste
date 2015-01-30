<?php

class IndexController extends Ymoney_Controller_Action {

    private $action_code_flag = '21354-';
    private $action_code_index = '52934';
    private $action_code_my = '48523';
    private $action_code_create = '23895';
    private $action_code_edit = '48754';
    private $action_code_account = '98347';
    private $action_code_stat = '23903';
    private $action_code_callback = '38432';

    public function init() {
        /* Initialize action controller here */
    }

    protected function initFB($act) {
        $config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $facebook = new Facebook(array('appId' => $config->ymoney->APP_ID, 'secret' => $config->ymoney->APP_SECRET));
        $accesstokenkey = implode('_', array('fb', $facebook->getAppId(), 'access_token'));
        if (isset($_SESSION[$accesstokenkey])) {
            $facebook->setAccessToken($_SESSION[$accesstokenkey]);
        }
//		print_r($facebook->getSignedRequest());
//		echo "<br>";
//		print_r($facebook->getAccessToken());
//		echo "<br>"; exit;
//		
        $user_id = $facebook->getUser();
        //echo "<br>user_id = ".$user_id."<br>";
        $auth_url = "http://www.facebook.com/dialog/oauth?client_id=" . $config->ymoney->APP_ID . "&client_secret=" .
                $config->ymoney->APP_SECRET . "&redirect_uri=" .
                urlencode($config->ymoney->APP_PAGE) . "&scope=email,publish_stream,user_photos";
        if ($user_id) {
            //echo "auth_url = ".$auth_url."<br>";
            // We have a user ID, so probably a logged in user.
            // If not, we'll get an exception, which we handle below.
            try {
                $user_profile = $facebook->api('/' . $user_id . '/permissions', 'GET');
//	        echo "permissions<br>";     
//	        print_r($user_profile['data'][0]); exit;
            } catch (FacebookApiException $e) {
                // If the user is logged out, you can have a 
                // user ID even though the access token is invalid.
                // In this case, we'll get an exception, so we'll
                // just ask the user to login again here.
                //$login_url = $facebook->getLoginUrl(); 
//	        echo "FacebookApiException ".$e;
//	        exit;
                echo("<script> top.location.href='" . $auth_url . "'</script>");
                exit;
            }
        } else {
            // No user, print a link for the user to login
            //$login_url = $facebook->getLoginUrl();
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
        $config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $facebook = new Facebook(array('appId' => $config->ymoney->APP_ID, 'secret' => $config->ymoney->APP_SECRET));
        //echo 'ymoneyNamespace->fb '.$ymoneyNamespace->fb; exit;
//    	print_r($facebook->getSignedRequest());
//		echo "<br>";
//		print_r($facebook->getAccessToken());
//		echo "<br>"; exit;
        $this->view->host = $config->ymoney->host;
        if ($ymoneyNamespace->fb) {
            $this->initFB($ymoneyNamespace->fb);
            //echo "end init fb<br>";
            $actArray = explode('-', $ymoneyNamespace->fb);
            $params = '';
            $act = $actArray[1];
            //echo "act $act<br>";
            switch ($act) {
                case $this->action_code_index: $act = 'index';
                    break;
                case $this->action_code_my: $act = 'my';
                    break;
                case $this->action_code_create: $act = 'create';
                    break;
                case $this->action_code_account: $act = 'account';
                    break;
                case $this->action_code_edit: $act = 'edit';
                    if (isset($actArray[2]))
                        $params = '?action_id=' . $actArray[2]; break;
                case $this->action_code_stat: $act = 'stat';
                    if (isset($actArray[2]))
                        $params = '?action_id=' . $actArray[2]; break;
                default: $act = 'index';
            }
            //echo "act $act<br>"; exit;
            $ymoneyNamespace->fb = false;
            //echo $this->_helper->url($act, 'actions', 'default').$params;exit;
            $this->_redirect($this->_helper->url($act, 'actions', 'default') . $params);
        }

        $signed_request = isset($_REQUEST['signed_request']) ? $_REQUEST['signed_request'] : '';
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);
        $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
        $user_id = false;
        $user_id = $facebook->getUser();

        // Интерфейс админа
        $this->view->admin = $this->_adminInteface($user_id);
        $this->view->appurl = $config->ymoney->APP_PAGE;
        $this->view->apphost = $config->ymoney->host;
        // Интерфейс пользователя
        $this->view->user_id = $user_id;
        $this->view->cancreate = $this->cancreate($user_id);
        // --- 

        if ($signed_request) {
            $this->view->signed_request = $signed_request;
        } else {
            $this->view->signed_request = $facebook->getSignedRequest();
        }

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
                        case $this->action_code_edit: $act = 'edit';
                            if (isset($actArray[2]))
                                $params = '?action_id=' . $actArray[2]; break;
                        case $this->action_code_stat: $act = 'stat';
                            if (isset($actArray[2]))
                                $params = '?action_id=' . $actArray[2]; break;
                        default: $act = 'index';
                    }
                    $this->_redirect($this->_helper->url($act, 'actions', 'default') . $params);
                } else {
                    $this->viewac($appdata);
                }
            }
            else
                $this->viewac($data['app_data']);
        } else {

            // Главная страница приложения

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


            foreach ($actions as $k => $action) {
                $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
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
                $this->_actionProcess($actions[$k]);
//                $start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
//                $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
//
//                if (!$action['date_end'] || $action['date_end'] == '0000-00-00 00:00:00') {
//                    if ($action['date_start'] != '0000-00-00 00:00:00') {
//                        $actions[$k]['dates'] = 'From ' . $this->_helper->dateFormat($action['date_start']);
//                        if (abs(mktime() - $start_stamp) >= 31536000) {
//                            $actions[$k]['dates'] .= ' ' . date('Y', $start_stamp);
//                        }
//                    } else {
//                        $actions[$k]['dates'] = 'Always';
//                    }
//                } elseif ($action['date_end'] != '0000-00-00 00:00:00') {
//                    if (date('n', $start_stamp) == date('n', $end_stamp)
//                            && abs($end_stamp - $start_stamp) < 31536000
//                            && (abs(mktime() - $start_stamp) < 31536000)
//                            && (abs(mktime() - $end_stamp) < 31536000)
//                    ) {
//                        $actions[$k]['dates'] = date('j', $start_stamp) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
//                    } elseif (
//                            abs($end_stamp - $start_stamp) < 31536000
//                            && (abs(mktime() - $start_stamp) < 31536000)
//                            && (abs(mktime() - $end_stamp) < 31536000)
//                    ) {
//                        $actions[$k]['dates'] = $this->_helper->dateFormat($action['date_start']) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
//                    } else {
//                        $actions[$k]['dates'] = $this->_helper->dateFormat($action['date_start']) . ' ' . date('Y', $start_stamp) . ' &mdash; ' .
//                                $this->_helper->dateFormat($action['date_end']) . ' ' . date('Y', $end_stamp);
//                    }
//
//                    // Если акция завершена - меняем статус
//                    if ($end_stamp < mktime()) {
//                        if ($action['completed'] == 'N') {
//                            $ac = $actionsModel->find($action['id'])->current();
//                            $ac->completed = 'Y';
//                            $ac->save();
//                            unset($ac);
//                            unset($actions[$k]);
//                        }
//                        continue;
//                    }
//
//                    if ($action['completed'] == 'Y') {
//                        $actions[$k]['left'] = 'collection complete';
//                    } else {
//                        $dateend = explode(" ", $action['date_end']);
//                        $dateend = $dateend[0];
//                        $dateend = explode("-", $dateend);
//                        $dtime = mktime(0, 0, 0, $dateend[1], $dateend[2], $dateend[0]);
//                        $ctime = mktime();
//                        $fivedays = 86400 * 5;
//                        if (($dtime - $ctime) <= $fivedays) {
//                            $dn = ceil(($dtime - $ctime) / 86400);
//                            if ($dn < 5 && $dn > 1) {
//                                $dt = " дня";
//                                $dl = " осталось ";
//                            } elseif ($dn == 1) {
//                                $dt = " день";
//                                $dl = " остался ";
//                            } elseif ($dn >= 5) {
//                                $dt = " дней";
//                                $dl = " осталось ";
//                            } else {
//                                $actions[$k]['left'] = 'collection complete';
//                            }
//                            if (($dtime - $ctime) > 0) {
//                                $actions[$k]['left'] = $dl . ceil(($dtime - $ctime) / 86400) . $dt;
//                            }
//                        }
//                    }
//                }

                if (strlen($action['name']) == 0) {
                    $actions[$k]['name'] = 'Not found';
                }
                if ($action['required_sum'] > 0 && $action['required_sum'] > ($action['current_sum'] + $action['all_sum'])) {
                    $actions[$k]['percents'] = (($action['current_sum'] + $action['all_sum']) / $action['required_sum']) * 100;
                    if ($actions[$k]['percents'] < 1 && ($action['current_sum'] + $action['all_sum']) > 0)
                        $actions[$k]['percents'] = 1;
                } elseif ($action['required_sum'] > 0 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum'])) {
                    $actions[$k]['percents'] = 100;
                } else {
                    $actions[$k]['percents'] = false;
                }

                if (strlen($action['name']) == 0) {
                    $actions[$k]['name'] = 'Not found';
                }

                if ($action['required_sum'] > 0) {
                    $actions[$k]['required_sumF'] = $this->_helper->numberFormat($action['required_sum']);
                } else {
                    $actions[$k]['required_sumF'] = 0;
                }
                if ($action['current_sum'] > 0 || $action['all_sum'] > 0) {
                    $actions[$k]['current_sumF'] = $this->_helper->numberFormat(($action['current_sum'] + $action['all_sum']));
                    if (($action['current_sum'] + $action['all_sum']) < 1000000 && $action['required_sum'] >= 1000000)
                        $actions[$k]['current_sum_suffix'] = ' rub. ';
                    else
                        $actions[$k]['current_sum_suffix'] = '';
                } else {
                    $actions[$k]['current_sumF'] = 0;
                }
            }
            $this->view->actions = $actions;
            
            foreach($this->view->actions as $k=>$v) {
                $tmpName = explode(' ', $v['name']);
                $i = 0;
                $s = count($tmpName);
                $tmp1 = "";
                while($i < $s) {
                    if(isset($tmpName[$i]) && mb_strlen($tmpName[$i], 'UTF-8') > 22) {
                        $tmpName[$i] = mb_substr($tmpName[$i], 0, 22, 'UTF-8');
                        array_splice($tmpName, ++$i, $s-$i);
                        $tmp1 = '...';
                        break;
                    }
                    
                    $i++;
                }
                $this->view->actions[$k]['filteredName'] = implode(' ', $tmpName) . $tmp1;
                $this->view->actions[$k]['description'] = $this->_descriptionCut($this->view->actions[$k]['description']);
            }

            $this->view->widthkoef = $config->ymoney->widthkoef;
            $ymoneyNamespace->fb = false;
            $script = "var fundAjaxUrl = '{$this->_helper->url('fundraisingstatsajax', 'actions', 'default')}';\n";
            $this->view->headScript()->prependScript($script, $type = 'text/javascript');
            $this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
            $this->view->headMeta()->setProperty('og:title', $config->ymoney->APP_NAME);
            $this->view->headMeta()->setProperty('og:type', "website");
            $this->view->headMeta()->setProperty('og:url', $config->ymoney->APP_PAGE);
            $this->view->headMeta()->setProperty('fb:app_id', $config->ymoney->APP_ID);
            $this->view->headMeta()->setProperty('og:image', $config->ymoney->host . '/i/app_75.png');
            $this->view->headMeta()->setProperty('og:description', $config->ymoney->description);
        }
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

    public function viewac($id) {
        $config = Zend_Registry::get('appConfig');
        $facebook = new Facebook(array('appId' => $config->ymoney->APP_ID, 'secret' => $config->ymoney->APP_SECRET));
        $user_id = $facebook->getUser();
        $id = intval($id);

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

            if (isset($action['id'])) {
                $action['left'] = null;
                $this->view->error = null;
                if ($this->view->user_id && ($action['blocked'] == 'Y' || $action['draft'] == 'Y') && $action['user_id'] == $this->view->user_id) {
                    if ($action['blocked'] == 'Y' && $action['draft'] == 'N')
                        $this->view->error = 'This collection has been blocked by the administrator';
                    if ($action['blocked'] == 'N' && $action['draft'] == 'Y')
                        $this->view->error = 'This collection is saved as a draft and has not been published';
                    if ($action['blocked'] == 'Y' && $action['draft'] == 'Y')
                        $this->view->error = 'This collection has been blocked by the administrator, saved as a draft and has not been published';
                } elseif (($action['blocked'] == 'Y' || $action['draft'] == 'Y') && !$this->view->admin) {
                    $this->_redirect($this->_helper->url('index', 'actions', 'default') . '?no_action=1');
                }


                if ($action['url'] != 'http://' && strlen($action['url'])) {
                    if (!strstr($action['url'], "http://")) {
                        $action['url'] = 'http://' . $action['url'];
                    }
                } else {
                    $action['url'] = '';
                }

                if ($action['completed'] == 'Y') {
                    $action['left'] = 'collection complete';
                }
                /* echo date('Y')." ";
                  echo date('Y', $start_stamp)." ";
                  echo (date('Y') - date('Y', $start_stamp)); */
                $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
                if ($action['date_end'] && $action['date_end'] != '0000-00-00 00:00:00' && $end_stamp < mktime()) {
                    if ($action['completed'] == 'N') {
                        $ac = $actionModel->find($action['id'])->current();
                        $ac->completed = 'Y';
                        $ac->save();
                        unset($ac);
                        unset($action);
                    }
                    $action['left'] = 'акция завершена';
                }
                $this->_actionProcess($action);
//                $start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
//                $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
//                if (!$action['date_end'] || $action['date_end'] == '0000-00-00 00:00:00') {
//                    if ($action['date_start'] != '0000-00-00 00:00:00') {
//                        $action['dates'] = 'From ' . $this->_helper->dateFormat($action['date_start']);
//                        if (abs(mktime() - $start_stamp) >= 31536000) {
//                            $action['dates'] .= ' ' . date('Y', $start_stamp);
//                        }
//                    } else {
//                        $action['dates'] = 'Always';
//                    }
//                    $action['starttext'] = $action['dates'];
//                } elseif ($action['date_end'] != '0000-00-00 00:00:00') {
//                    // Месяц и год совпадают (+/- 1)
//                    if (
//                            date('n', $start_stamp) == date('n', $end_stamp)
//                            &&
//                            abs($end_stamp - $start_stamp) < 31536000 &&
//                            (abs(mktime() - $start_stamp) < 31536000) &&
//                            (abs(mktime() - $end_stamp) < 31536000)
//                    ) {
//                        $action['dates'] = date('j', $start_stamp) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
//                        $action['starttext'] = 'From ' . $this->_helper->dateFormat($action['date_start']);
//                    }
//                    // Месяцы не совпадают, годы совпадают (+/- 1)
//                    elseif (
//                            abs($end_stamp - $start_stamp) < 31536000 &&
//                            (abs(mktime() - $start_stamp) < 31536000) &&
//                            (abs(mktime() - $end_stamp) < 31536000)
//                    ) {
//                        $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
//                        $action['starttext'] = 'From ' . $this->_helper->dateFormat($action['date_start']);
//                    }
//                    // Месяцы и годы не совпадают
//                    else {
//                        $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' ' . date('Y', $start_stamp) . ' &mdash; ' .
//                                $this->_helper->dateFormat($action['date_end']) . ' ' . date('Y', $end_stamp);
//                        $action['starttext'] = 'From ' . $this->_helper->dateFormat($action['date_start'] . ' ' . date('Y', $start_stamp));
//                    }
//                    // Если акция завершена - меняем статус
//                    if ($end_stamp < mktime()) {
//                        if ($action['completed'] == 'N') {
//                            $ac = $actionModel->find($id)->current();
//                            $ac->completed = 'Y';
//                            $ac->save();
//                            unset($ac);
//                        }
//                        $action['left'] = 'collection complete';
//                    } else {
//                        if ($action['completed'] == 'Y') {
//                            $action['left'] = 'collection complete';
//                        } else {
//                            $dateend = explode(" ", $action['date_end']);
//                            $dateend = $dateend[0];
//                            $dateend = explode("-", $dateend);
//                            $dtime = mktime(0, 0, 0, $dateend[1], $dateend[2], $dateend[0]);
//                            $ctime = time();
//                            $fivedays = 86400 * 5;
//                            //echo $dtime-$ctime;
//                            if (($dtime - $ctime) <= $fivedays) {
//                                $dn = ceil(($dtime - $ctime) / 86400);
//                                if ($dn < 5 && $dn > 1) {
//                                    $dt = " дня";
//                                    $dl = " осталось ";
//                                } elseif ($dn == 1) {
//                                    $dt = " день";
//                                    $dl = " остался ";
//                                } elseif ($dn >= 5) {
//                                    $dt = " дней";
//                                    $dl = " осталось ";
//                                } else {
//                                    $action['left'] = 'collection complete';
//                                }
//                                if (($dtime - $ctime) > 0) {
//                                    $action['left'] = $dl . ceil(($dtime - $ctime) / 86400) . $dt;
//                                }
//                            }
//                        }
//                    }
//                }

                if ($action['group'] == 1) {
                    $usersactionModel = new Application_Model_DbTable_Usersaction();
                    $friendlist = $usersactionModel->selectfriends($action['id']);
                    $action['friends'] = $friendlist;
                    $action['required_sum_group'] = $action['required_sum'] / (count($action['friends']) + 1) * 1.005;
                    $this->view->redirect_friends_url = 'https://money.yandex.ru/direct-payment.xml?receiver=' .
                            $action['receiver'] . '&Formcomment=' . urlencode("For collection in Facebook «" . $action['name'] . "»") . '&destination=' .
                            urlencode('For collection «'.$action['name'].'» from user facebook.com/' . $user_id . '. To send money anonymously, please delete all comments.') .
                            '&sum=' . ($action['required_sum_group']) . '&_openstat=socapp;fben;p2p;list&label=fben_' . $action['id'];
                } else {
                    $action['required_sum_group'] = 0;
                }

                if ($action['left'] == 'collection complete') {
                    $this->view->error = 'This collection is not yet finished, but you may also support other good causes.';
                }

                if ($action['required_sum'] > 0 && $action['required_sum'] > ($action['current_sum'] + $action['all_sum'])) {
                    $action['percents'] = (($action['current_sum'] + $action['all_sum']) / $action['required_sum']) * 100;
                    if ($action['percents'] < 1 && ($action['current_sum'] + $action['all_sum']) > 0)
                        $action['percents'] = 1;
                } elseif ($action['required_sum'] > 0 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum'])) {
                    $action['percents'] = 100;
                } else {
                    $action['percents'] = false;
                }

                $action['description'] = nl2br($action['description']);

                $action['short'] = str_replace('<br />', ' ', $action['description']);
                $short = $this->uni_strsplit($action['short']);
                $action['short'] = '';
                foreach ($short as $sym) {
                    if (ord($sym) != 10 && ord($sym) != 13) {
                        $action['short'] .= $sym;
                    }
                }

                //$action['short'] = mb_substr(addslashes($action['short']), 0, 275).'…';
                $action['short'] = addslashes($action['short']);
                if ($action['required_sum'] > 0) {
                    $action['required_sumF'] = $this->_helper->numberFormat($action['required_sum'], true);
                } else {
                    $action['required_sumF'] = 0;
                }
                if ($action['current_sum'] > 0 || $action['all_sum'] > 0) {
                    $action['current_sumF'] = $this->_helper->numberFormat(($action['current_sum'] + $action['all_sum']), true);
                } else {
                    $action['current_sumF'] = 0;
                }
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
                
                $this->view->widthkoef = $config->ymoney->bigwidthkoef;
                $photoModel = new Application_Model_DbTable_Photo();
                $this->view->selectedphotos = array();

                $this->view->photos = array();
                if ($facebook->getUser() != 0) {
                    $this->view->selectedphotos = $photoModel->getPhotosByAction($action['id']);
                    foreach ($this->view->selectedphotos as $photo) {
                        $fql = "SELECT pid, src, src_big FROM photo WHERE pid ='" . $photo . "'";
                        $param = array(
                            'method' => 'fql.query',
                            'query' => $fql,
                            'callback' => '',
                            'access_token' => $facebook->getAccessToken()
                        );
                        $ar = $facebook->api($param);
                        if(count($ar) == 0) {
                            $where = $photoModel->getAdapter()->quoteInto('pid = ?', $photo);
                            $photoModel->delete($where);
                        } else {
                            $this->view->photos[] = $ar[0];
                        }
                    }
                } else {
                    $ar = $photoModel->getFullPhotosByAction($action['id']);
                    foreach($ar as $k=>$v) {
                        $url = $v['src'];
                        $Headers = @get_headers($url);
                        // проверяем ли ответ от сервера с кодом 200 - ОК
                        if(strpos($Headers[0], '200')) {
                            $this->view->photos[] = $v;
                        } else {
                            $where = $photoModel->getAdapter()->quoteInto('pid = ?', $v['pid']);
                            $photoModel->delete($where);
                        }
                    }
                }
                
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

                $this->view->share = $config->ymoney->APP_PAGE . '?app_data=' . $action['id'] . '&t=' . urlencode($action['name']);
                $this->view->redirect_url = $config->ymoney->APP_PAGE . '?app_data=' . $action['id'];
                $this->view->appPicture = $config->ymoney->host . '/i/app_75_new.png';

                $this->view->headTitle($action['name']);
                $this->view->headMeta(strip_tags($action['description']), 'description');

                $this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
                $this->view->headMeta()->setProperty('og:title', $action['name']);
                $this->view->headMeta()->setProperty('og:type', "website");
                $this->view->headMeta()->setProperty('og:url', $this->view->redirect_url);
                $this->view->headMeta()->setProperty('fb:app_id', $config->ymoney->APP_ID);
                $this->view->headMeta()->setProperty('og:image', $config->ymoney->host . '/i/app_75.png');
                $this->view->headMeta()->setProperty('og:description', strip_tags($action['description']));
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
          $config = Zend_Registry::get('appConfig');
          ZenYandexClient::setClientId($config->ymoney->client_id);
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
        $config = Zend_Registry::get('appConfig');
        if ($this->_getParam('action_id') && intval($this->_getParam('action_id')) > 0) {
            $this->_redirect($config->ymoney->APP_PAGE_PAGE . '&app_data=' . intval($this->_getParam('action_id')) . '&ref=nf');
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
    
    private function _actionProcess(&$action) {
        $start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
        $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
        if (!$action['date_end'] || $action['date_end'] == '0000-00-00 00:00:00') {
            if ($action['date_start'] != '0000-00-00 00:00:00') {
                $action['dates'] = 'From ' . $this->_helper->dateFormat($action['date_start']);
                if (date('Y') - date('Y', $start_stamp) >= 1) {
                    $action['dates'] .= ' ' . date('Y', $start_stamp);
                }
            } else {
                $action['dates'] = 'Always';
            }
            $action['starttext'] = $action['dates'];
        } elseif ($action['date_end'] != '0000-00-00 00:00:00') {
            // Месяц и год совпадают (+/- 1)
            if (date('n', $start_stamp) == date('n', $end_stamp)
                    && (date('Y', $end_stamp) - date('Y', $start_stamp) < 1)
                    && (date('Y') - date('Y', $start_stamp) < 1)
                    && (date('Y') - date('Y', $end_stamp) < 1)
            ) {
                $action['dates'] = date('j', $start_stamp) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
                $action['starttext'] = 'From ' . $this->_helper->dateFormat($action['date_start']);
            }
            // Месяцы не совпадают, годы совпадают (+/- 1)
            elseif ((date('Y', $end_stamp) - date('Y', $start_stamp) < 1)
                    && (date('Y') - date('Y', $start_stamp) < 1)
                    && (date('Y') - date('Y', $end_stamp) < 1)
            ) {
                $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
                $action['starttext'] = 'From ' . $this->_helper->dateFormat($action['date_start']);
            }
            // Месяцы и годы не совпадают
            else {
                $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' ' . date('Y', $start_stamp) . ' &mdash; ' .
                        $this->_helper->dateFormat($action['date_end']) . ' ' . date('Y', $end_stamp);
                $action['starttext'] = 'From ' . $this->_helper->dateFormat($action['date_start'] . ' ' . date('Y', $start_stamp));
            }

            // Если акция завершена - меняем статус
            $action['left'] = null;
            if ($end_stamp < mktime()) {
                $action['left'] = 'collection complete';
            } else {
                if ($action['completed'] == 'Y') {
                    $action['left'] = 'collection complete';
                } else {
                    $dateend = explode(" ", $action['date_end']);
                    $dateend = $dateend[0];
                    $dateend = explode("-", $dateend);
                    $dtime = mktime(0, 0, 0, $dateend[1], $dateend[2], $dateend[0]);
                    $ctime = mktime();
                    $fivedays = 86400 * 5;
                    if (($dtime - $ctime) <= $fivedays) {
                        $dn = ceil(($dtime - $ctime) / 86400);
                        if ($dn < 5 && $dn > 1) {
                            $dt = " days left";
//                            $dl = "";
                        } elseif ($dn == 1) {
                            $dt = " day left";
//                            $dl = "";
                        } elseif ($dn >= 5) {
                            $dt = " days left";
//                            $dl = " days ";
                        } else {
                            $action['left'] = 'collection complete';
                        }
                        if (($dtime - $ctime) > 0) {
                            $action['left'] = /*$dl . */ceil(($dtime - $ctime) / 86400) . $dt;
                        }
                    }
                }
            }
        }
    }
    
}

