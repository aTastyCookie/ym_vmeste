<?php

class ActionsController extends Ymoney_Controller_Action {

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
        $config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');

        require_once 'vkapi.class.php';

        $this->VK = new vkapi($config->ymoney->APP_ID, $config->ymoney->APP_SECRET, $ymoneyNamespace->vk['api_url']);

        $this->view->appurlorigin = $config->ymoney->APP_PAGE_ORIGIN;
        $this->view->appdesc = $config->ymoney->description;
        $this->view->appname = $config->ymoney->APP_NAME;
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

    protected function removeCRLF($string) {
        $newstring = '';
        for ($i = 0; $i < strlen($string); $i++) {
            if (ord($string{$i}) != 10 && ord($string{$i}) != 13) {
                $newstring .= $string{$i};
            } else {
                break;
            }
        }
        return $newstring;
    }

    public function sortactions($a)
    {
    	$b = array();
    	$c = array();
    	$keys = array_keys($a);
    	shuffle($keys);
    	foreach($keys as $key=>$v) {
    		if(!in_array($a[$v]['id'], $c)) {
    			$b[] = $a[$v];
    			$c[] = $a[$v]['id'];
    		}
    	}
    
    
    	return $b;
    }
    
    protected function initFB() {
        $config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');

        $user_id = $ymoneyNamespace->vk['viewer_id'];
        //d($user_id);
        $resp = $this->VK->api('getUserSettings', array(
            'uid' => $user_id
                ));
        $this->view->vk_user_settings = $resp['response'];
    }

    public function saveuser() {

        $config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        //echo "saveuser...<br>"; exit;
        //Сохраняем пользователя в БД
        $userModel = new Application_Model_DbTable_Users();

        if (!$userModel->find($user_id)->current()) {
            $user_profile = $this->VK->api('getProfiles', array(
                'uids' => $user_id,
                'fields' => 'first_name,last_name,nickname,domain'
                    )
            );

            //d($user_id);

            if ($user_profile['response'][0]) {
                $user_profile = $user_profile['response'][0];
            } else {
                $user_profile = false;
            }
            if ($user_profile) {
                $fields = array(
                    'id' => $user_id,
                    'name' => implode(' ', array($user_profile['first_name'], $user_profile['nickname'], $user_profile['last_name'])),
                    'firstname' => $user_profile['first_name'],
                    'lastname' => $user_profile['last_name'],
                    'url' => 'http://www.vk.com/' . $user_profile['domain']
                );

                $userModel->insert($fields);
                return true;
            } else {
                //echo "<script>window.location.reload(true);</script>";
                echo "Не могу получить профиль. ";
                return false;
            }
        } else
            $user_profile = $this->VK->api('getProfiles', array(
                'uids' => $user_id,
                'fields' => 'first_name,last_name,nickname,domain'
                    )
            );

            if ($user_profile['response'][0]) {
                $user_profile = $user_profile['response'][0];
            } else {
                $user_profile = false;
            }
            if ($user_profile) {
                $fields = array(
                    'name' => implode(' ', array($user_profile['first_name'], $user_profile['nickname'], $user_profile['last_name'])),
                    'firstname' => $user_profile['first_name'],
                    'lastname' => $user_profile['last_name'],
                    'url' => 'http://vk.com/' . $user_profile['domain']
                );
                
                $where = $userModel->getAdapter()->quoteInto('id = ?', $user_id);
                $userModel->update($fields, $where);
            }
            return true;
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

    public function indexAction() {
        $config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $this->view->appurl = $config->ymoney->APP_PAGE;
        $this->view->appid = $config->ymoney->APP_ID;
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $this->view->user_id = $user_id;
        // Интерфейс админа
        $this->view->admin = $this->_adminInteface($user_id);
        $this->view->cancreate = $this->cancreate($user_id);
        $script = "var pageAjaxUrl = '{$this->_helper->url('actionspage', 'actions', 'default')}';\n";
        $script .= "var fundAjaxUrl = '{$this->_helper->url('fundraisingstatsajax', 'actions', 'default')}';\n";
        $this->view->headScript()->prependScript($script, $type = 'text/javascript');

        $this->view->headTitle($config->ymoney->APP_NAME);
        $this->view->headMeta($config->ymoney->description2, 'description');

        $this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
        $this->view->headMeta()->setProperty('vk:title', $config->ymoney->APP_NAME);
        $this->view->headMeta()->setProperty('vk:type', "website");
        $this->view->headMeta()->setProperty('vk:url', $config->ymoney->APP_PAGE_ORIGIN);
        $this->view->headMeta()->setProperty('vk:app_id', $config->ymoney->APP_ID);
        $this->view->headMeta()->setProperty('vk:image', $config->ymoney->host . '/i/app_75.jpg');
        $this->view->headMeta()->setProperty('vk:description', $config->ymoney->description2);
        
        // Все акции
        $actionsModel = new Application_Model_DbTable_Action();
        $new = null;
        $top = null;
        $this->view->selected = 'all';
        if ($this->_getParam('new') && $this->_getParam('new') == 'y') {
            $new = true;
            $this->view->selected = 'new';
        }

        if ($this->_getParam('top') && $this->_getParam('top') == 'y') {
            $top = true;
            $this->view->selected = 'top';
        }

        $search = null;
        if ($this->_getParam('search') && mb_strlen($this->_getParam('search')) > 1) {
            $search = $this->_rqfs($this->_getParam('search'));
            $this->view->selected = null;
        }

        $this->view->search = $search;
        $blocked = null;
        $hidden = null;
        $completed = null;
        if ($this->view->admin && $this->_getParam('hidden') && $this->_getParam('hidden') == 'y') {
            $hidden = true;
            $this->view->selected = 'hidden';
        }
        if ($this->view->admin && $this->_getParam('blocked') && $this->_getParam('blocked') == 'y') {
            $blocked = true;
            $this->view->selected = 'blocked';
        }
        $pop = null;
        if ($this->_getParam('pop') && $this->_getParam('pop') == 'y') {
            $pop = true;
            $this->view->selected = 'pop';
        }
        if ($this->view->admin && $this->_getParam('completed') && $this->_getParam('completed') == 'y') {
            $completed = true;
            $this->view->selected = 'completed';
        }

        $actions = $actionsModel->getActions(null, $top, null, $new, $search, $hidden, $blocked, $pop, 10, 0, null, $completed);

        foreach ($actions as $k => $action) {
            
            $this->_action2Process($actions[$k]);
            
            if ($action['required_sum'] > 0 && $action['required_sum'] > ($action['current_sum'] + $action['all_sum'])) {
                $actions[$k]['percents'] = (($action['current_sum'] + $action['all_sum']) / $action['required_sum']) * 100;
                if ($actions[$k]['percents'] < 1 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum']))
                    $actions[$k]['percents'] = 1;
            } elseif ($action['required_sum'] > 0 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum'])) {
                $actions[$k]['percents'] = 100;
            } else {
                $actions[$k]['percents'] = false;
            }

            if (strlen($action['name']) == 0) {
                $actions[$k]['name'] = 'Без названия';
            }

            if ($action['required_sum'] > 0) {
                $actions[$k]['required_sumF'] = $this->_helper->numberFormat($action['required_sum']);
            } else {
                $actions[$k]['required_sumF'] = 0;
            }

            if ($action['current_sum'] > 0 || $action['all_sum'] > 0) {
                $actions[$k]['current_sumF'] = $this->_helper->numberFormat(($action['current_sum'] + $action['all_sum']));
            } else {
                $actions[$k]['current_sumF'] = 0;
            }

            if (strlen($action['name']) == 0) {
                $actions[$k]['name'] = 'Без названия';
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
            // убираем описание, которое может конфликтовать с json
            unset($this->view->actions[$k]['description']);
        }
        
        $this->view->widthkoef = $config->ymoney->widthkoef;
        $this->view->error = false;
        if ($this->_getParam('no_action') && $this->_getParam('no_action') == 1) {
            $this->view->error = true;
        }
    }

    public function myAction() {
        $this->initFB();
        $config = Zend_Registry::get('appConfig');
        $script = "var pageAjaxUrl = '{$this->_helper->url('myactionspage', 'actions', 'default')}';\n";
        $script .= "var fundAjaxUrl = '{$this->_helper->url('fundraisingstatsajax', 'actions', 'default')}';\n";
        $this->view->headScript()->prependScript($script, $type = 'text/javascript');

        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $this->view->admin = $this->_adminInteface($user_id);
        $this->view->user_id = $user_id;

        $this->view->appurl = $config->ymoney->APP_PAGE;
        $this->view->appid = $config->ymoney->APP_ID;
        $this->view->who = $this->_getParam('who');
        $this->view->newId = $this->_getParam('newId', false);
        

        if (!$this->saveuser()) {
            echo "Произошла ошибка.";
            //echo "<script>window.location.reload(true);</script>";
            //$this->_redirect($this->_helper->url('index', 'actions', 'default'));
            exit;
        }

        $this->view->headTitle($config->ymoney->APP_NAME);
        $this->view->headMeta($config->ymoney->description2, 'description');

        $this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
        $this->view->headMeta()->setProperty('vk:title', $config->ymoney->APP_NAME);
        $this->view->headMeta()->setProperty('vk:type', "website");
        $this->view->headMeta()->setProperty('vk:url', $config->ymoney->APP_PAGE_ORIGIN);
        $this->view->headMeta()->setProperty('vk:app_id', $config->ymoney->APP_ID);
        $this->view->headMeta()->setProperty('vk:image', $config->ymoney->host . '/i/app_75.jpg');
        $this->view->headMeta()->setProperty('vk:description', $config->ymoney->description2);

        $this->view->cancreate = $this->cancreate($user_id);
        // Проверяем, давал ли пользователь права доступа приложения для Яндекс.Денег
        $zayac = $this->_verifyUser($this->action_code_flag . $this->action_code_my);
        $account = $zayac->getAccountInformation();

        // Все акции
        $actionsModel = new Application_Model_DbTable_Action();
        $actionsModel->setToken($user_id, $ymoneyNamespace->access_token, $account['account']);
        $drafts = null;
        $blocked = false;
        $completed = null;
        $this->view->selected = 'all';
        if ($this->_getParam('drafts') && $this->_getParam('drafts') == 'y') {
            $drafts = true;
            $this->view->selected = 'drafts';
        }

        $search = null;
        if ($this->_getParam('search') && mb_strlen($this->_getParam('search')) > 1) {
            $search = $this->_rqfs($this->_getParam('search'));
            $this->view->selected = null;
        }

        $this->view->search = $search;

        if ($this->_getParam('blocked') && $this->_getParam('blocked') == 'y') {
            $blocked = true;
            $this->view->selected = 'blocked';
        }

        $actions = $actionsModel->getActions($user_id, null, null, null, null, null, true);
        if (count($actions))
            $this->view->hasBlocked = true;
        else
            $this->view->hasBlocked = false;

        if ($this->_getParam('completed') && $this->_getParam('completed') == 'y') {
            $completed = true;
            $this->view->selected = 'completed';
        }
        
        $asPages = null;
        if ($this->_getParam('pages') && $this->_getParam('pages') == 'y') {
            $asPages = true;
            $this->view->selected = 'pages';
        }
        $onlyLink = null;
        if ($this->_getParam('hidden') && $this->_getParam('hidden') == 'y') {
            $onlyLink = true;
            $this->view->selected = 'hidden';
        }
        
        $group = 2;
        
        $actions = $actionsModel->getActions($user_id, null, $drafts, null, $search, null, $blocked, null, 10, 0, null, $completed, $group, $asPages, $onlyLink);
        
//        die(var_dump($actions));
        //$actions = $this->sortactions($actions);
        
        foreach ($actions as $k => $action) {
            
            $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
            // Если акция завершена - меняем статус
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
            $this->_action2Process($actions[$k]);
            
//            $start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
//            $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
//            if ($action['completed'] == 'Y') {
//                $actions[$k]['left'] = 'акция завершена';
//            }
//            if (!$action['date_end'] || $action['date_end'] == '0000-00-00 00:00:00') {
//                if ($action['date_start'] != '0000-00-00 00:00:00') {
//                    $actions[$k]['dates'] = 'С ' . $this->_helper->dateFormat($action['date_start']);
//                    if (abs(mktime() - $start_stamp) >= 31536000) {
//                        $actions[$k]['dates'] .= ' ' . date('Y', $start_stamp);
//                    }
//                } else {
//                    $actions[$k]['dates'] = 'Всегда';
//                }
//            } elseif ($action['date_end'] != '0000-00-00 00:00:00') {
//                if (date('n', $start_stamp) == date('n', $end_stamp)
//                        && abs($end_stamp - $start_stamp) < 31536000
//                        && (abs(mktime() - $start_stamp) < 31536000)
//                        && (abs(mktime() - $end_stamp) < 31536000)
//                ) {
//                    $actions[$k]['dates'] = date('j', $start_stamp) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
//                } elseif (
//                        abs($end_stamp - $start_stamp) < 31536000
//                        && (abs(mktime() - $start_stamp) < 31536000)
//                        && (abs(mktime() - $end_stamp) < 31536000)
//                ) {
//                    $actions[$k]['dates'] = $this->_helper->dateFormat($action['date_start']) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
//                } else {
//                    $actions[$k]['dates'] = $this->_helper->dateFormat($action['date_start']) . ' ' . date('Y', $start_stamp) . ' &mdash; ' .
//                            $this->_helper->dateFormat($action['date_end']) . ' ' . date('Y', $end_stamp);
//                }
//                // Если акция завершена - меняем статус
//                if ($end_stamp < mktime()) {
//                    if ($action['completed'] == 'N') {
//                        $ac = $actionsModel->find($action['id'])->current();
//                        $ac->completed = 'Y';
//                        $ac->save();
//                        unset($ac);
//                        unset($actions[$k]);
//                    }
////                    continue;
//                }
//
//                if ($action['completed'] == 'Y') {
//                    $actions[$k]['left'] = 'акция завершена';
//                } else {
//                    $dateend = explode(" ", $action['date_end']);
//                    $dateend = $dateend[0];
//                    $dateend = explode("-", $dateend);
//                    $dtime = mktime(0, 0, 0, $dateend[1], $dateend[2], $dateend[0]);
//                    $ctime = mktime();
//                    $fivedays = 86400 * 5;
//                    if (($dtime - $ctime) <= $fivedays) {
//                        $dn = ceil(($dtime - $ctime) / 86400);
//                        if ($dn < 5 && $dn > 1) {
//                            $dt = " дня";
//                            $dl = " осталось ";
//                        } elseif ($dn == 1) {
//                            $dt = " день";
//                            $dl = " остался ";
//                        } elseif ($dn >= 5) {
//                            $dt = " дней";
//                            $dl = " осталось ";
//                        } else {
//                            $actions[$k]['left'] = 'акция завершена';
//                        }
//                        if (($dtime - $ctime) > 0) {
//                            $actions[$k]['left'] = $dl . ceil(($dtime - $ctime) / 86400) . $dt;
//                        }
//                    }
//                }
//            }
            if ($action['required_sum'] > 0 && $action['required_sum'] > ($action['current_sum'] + $action['all_sum'])) {
                $actions[$k]['percents'] = (($action['current_sum'] + $action['all_sum']) / $action['required_sum']) * 100;
                if ($actions[$k]['percents'] < 1 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum']))
                    $actions[$k]['percents'] = 1;
            } elseif ($action['required_sum'] > 0 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum'])) {
                $actions[$k]['percents'] = 100;
            } else {
                $actions[$k]['percents'] = false;
            }
            
            if($action['group']==1) {
            	$usersactionModel = new Application_Model_DbTable_Usersaction();
            	$friendlist = $usersactionModel->selectfriends($action['id']);
            	$actions[$k]['friends'] = $friendlist;
            	$actions[$k]['required_sum_group'] = $action['required_sum']/(count($actions[$k]['friends'])+1)*1.005;
            } else {
            	$actions[$k]['required_sum_group'] = 0;
            }

            if (strlen($action['name']) == 0) {
                $actions[$k]['name'] = 'Без названия';
            }

            if ($action['required_sum'] > 0) {
                $actions[$k]['required_sumF'] = $this->_helper->numberFormat($action['required_sum']);
            } else {
                $actions[$k]['required_sumF'] = 0;
            }
			$actions[$k]['current_sum_suffix'] = '';
            if ($action['current_sum'] > 0 || $action['all_sum'] > 0) {
                $actions[$k]['current_sumF'] = $this->_helper->numberFormat(($action['current_sum'] + $action['all_sum']));
                if (($action['current_sum'] + $action['all_sum']) < 1000000 && $action['required_sum'] >= 1000000)
                    $actions[$k]['current_sum_suffix'] = ' руб. ';
                
                    
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
            while($i <= $s) {
                if(isset($tmpName[$i]) && mb_strlen($tmpName[$i], 'UTF-8') > 22) {
                    $tmpName[$i] = mb_substr($tmpName[$i], 0, 22, 'UTF-8');
                    array_splice($tmpName, ++$i, $s-$i);
                    $tmp1 = '...';
                    break;
                }

                $i++;
            }
            $this->view->actions[$k]['filteredName'] = implode(' ', $tmpName) . $tmp1;
            // убираем описание, которое может конфликтовать с json
            unset($this->view->actions[$k]['description']);
        }

        $this->view->error = false;
        if ($this->_getParam('no_action') && $this->_getParam('no_action') == 1) {
            $this->view->error = true;
        }
    }

    public function actionspageAction() {
        $jsonData = array('li' => array());
        $config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        //var_dump($user_id);
        // Интерфейс админа
        $admin = $this->_adminInteface($user_id);

        // акции
        $actionsModel = new Application_Model_DbTable_Action();
        $new = null;
        $pop = null;
        $top = null;
        $blocked = null;
        $hidden = null;
        $selected = $this->_getParam('selected');

        $selected = true;

        $search = null;
        if ($this->_getParam('search') && $this->_getParam('search') != 'false') {
            $search = $this->_rqfs($this->_getParam('search'));
            $selected = null;
        }
        $jsonData['next'] = false;
        $page = $this->_getParam('page');
        if ($this->_getParam('next') == 'true') {
            $offset = $page * 9;
            $jsonData['prev'] = true;
        } else {
            $offset = ($page - 2) * 9;
            if ($offset == 0)
                $jsonData['prev'] = false;
            else
                $jsonData['prev'] = true;
        }

        $actions = $actionsModel->getActions(null, $top, null, $new, $search, $hidden, $blocked, $pop, 10, $offset);
        $jsonData['actions'] = $actions;
        //echo count($actions);
        $li = array();
        $i = 1;
        foreach ($actions as $k => $action) {
            $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
            // Если акция завершена - меняем статус
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
            
            $this->_action2Process($actions[$k]);
//            $start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
//            $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
//            if ($action['completed'] == 'Y') {
//                $actions[$k]['left'] = 'акция завершена';
//            }
//            if (!$action['date_end'] || $action['date_end'] == '0000-00-00 00:00:00') {
//                if ($action['date_start'] != '0000-00-00 00:00:00') {
//                    $actions[$k]['dates'] = 'С ' . $this->_helper->dateFormat($action['date_start']);
//                    if (abs(mktime() - $start_stamp) >= 31536000) {
//                        $actions[$k]['dates'] .= ' ' . date('Y', $start_stamp);
//                    }
//                } else {
//                    $actions[$k]['dates'] = 'Всегда';
//                }
//            } elseif ($action['date_end'] != '0000-00-00 00:00:00') {
//                if (date('n', $start_stamp) == date('n', $end_stamp)
//                        && abs($end_stamp - $start_stamp) < 31536000
//                        && (abs(mktime() - $start_stamp) < 31536000)
//                        && (abs(mktime() - $end_stamp) < 31536000)
//                ) {
//                    $actions[$k]['dates'] = date('j', $start_stamp) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
//                } elseif (
//                        abs($end_stamp - $start_stamp) < 31536000
//                        && (abs(mktime() - $start_stamp) < 31536000)
//                        && (abs(mktime() - $end_stamp) < 31536000)
//                ) {
//                    $actions[$k]['dates'] = $this->_helper->dateFormat($action['date_start']) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
//                } else {
//                    $actions[$k]['dates'] = $this->_helper->dateFormat($action['date_start']) . ' ' . date('Y', $start_stamp) . ' &mdash; ' .
//                            $this->_helper->dateFormat($action['date_end']) . ' ' . date('Y', $end_stamp);
//                }
//                // Если акция завершена - меняем статус
//                if ($end_stamp < mktime()) {
//                    if ($action['completed'] == 'N') {
//                        $ac = $actionsModel->find($action['id'])->current();
//                        $ac->completed = 'Y';
//                        $ac->save();
//                        unset($ac);
//                        unset($actions[$k]);
//                    }
////                    continue;
//                }
//
//                if ($action['completed'] == 'Y') {
//                    $actions[$k]['left'] = 'акция завершена';
//                } else {
//                    $dateend = explode(" ", $action['date_end']);
//                    $dateend = $dateend[0];
//                    $dateend = explode("-", $dateend);
//                    $dtime = mktime(0, 0, 0, $dateend[1], $dateend[2], $dateend[0]);
//                    $ctime = mktime();
//                    $fivedays = 86400 * 5;
//                    if (($dtime - $ctime) <= $fivedays) {
//                        $dn = ceil(($dtime - $ctime) / 86400);
//                        if ($dn < 5 && $dn > 1) {
//                            $dt = " дня";
//                            $dl = " осталось ";
//                        } elseif ($dn == 1) {
//                            $dt = " день";
//                            $dl = " остался ";
//                        } elseif ($dn >= 5) {
//                            $dt = " дней";
//                            $dl = " осталось ";
//                        } else {
//                            $actions[$k]['left'] = 'акция завершена';
//                        }
//                        if (($dtime - $ctime) > 0) {
//                            $actions[$k]['left'] = $dl . ceil(($dtime - $ctime) / 86400) . $dt;
//                        }
//                    }
//                }
//            }
            if ($action['required_sum'] > 0 && $action['required_sum'] > ($action['current_sum'] + $action['all_sum'])) {
                $actions[$k]['percents'] = (($action['current_sum'] + $action['all_sum']) / $action['required_sum']) * 100;
                if ($actions[$k]['percents'] < 1 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum']))
                    $actions[$k]['percents'] = 1;
            } elseif ($action['required_sum'] > 0 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum'])) {
                $actions[$k]['percents'] = 100;
            } else {
                $actions[$k]['percents'] = false;
            }

            if (strlen($action['name']) == 0) {
                $actions[$k]['name'] = 'Без названия';
            }

            if ($action['required_sum'] > 0) {
                $actions[$k]['required_sumF'] = $this->_helper->numberFormat($action['required_sum']);
            } else {
                $actions[$k]['required_sumF'] = 0;
            }

            if ($action['current_sum'] > 0 || $action['all_sum'] > 0) {
                $actions[$k]['current_sumF'] = $this->_helper->numberFormat(($action['current_sum'] + $action['all_sum']));
            } else {
                $actions[$k]['current_sumF'] = 0;
            }

            if ($actions[$k]['percents'] >= 0 && $actions[$k]['completed'] == 'N') {
                if ($actions[$k]['percents'] == 0) {
                    $bkground = '0';
                } elseif ($actions[$k]['percents'] <= 33) {
                    $bkground = '0';
                } elseif ($actions[$k]['percents'] <= 67) {
                    $bkground = '-7';
                } else {
                    $bkground = '-14';
                }
            } else {
                $bkground = '-21';
            }
            
            $tmpName = explode(' ', $action['name']);
            $j = 0;
            $s = count($tmpName);
            $tmp1 = $tmp2 = "";
            while($j < $s) {
                if(isset($tmpName[$j]) && mb_strlen($tmp2.$tmpName[$j], 'UTF-8') > 47) {
                    $tmp1 = '...';
                    break;
                }
				$tmp2 .= $tmpName[$j].' ';
                $j++;
            }
			$actions[$k]['filteredName'] = trim($tmp2) . $tmp1;
			
			
            $last = ($i % 3) == 0 ? " last" : '';
            $left = isset($actions[$k]['left']) ? $actions[$k]['left'] : '';
            $toplink = ($actions[$k]['top'] == 'Y' && $admin) ? '<img src="/i/symbol-favi-24.png" alt="Акция в ТОПе"  class="mb_ico" />' : '';
            $blockedlink = ($actions[$k]['blocked'] == 'Y' && $admin) ? '<img src="/i/symbol-rejected-24.png" alt="Акция заблокирована администратором"  class="mb_ico" />' : '';
            $name = mb_strlen($actions[$k]['filteredName'], "UTF-8") <= 53 ? $actions[$k]['filteredName'] : mb_substr($actions[$k]['filteredName'], 0, 52, "UTF-8") . "…";
            $username = mb_strlen($actions[$k]['Username'], "UTF-8") <= 18 ? $actions[$k]['Username'] : mb_substr($actions[$k]['Firstname'], 0, 1, "UTF-8") . '. ' . $actions[$k]['Lastname'];
            $pagename = mb_strlen($actions[$k]['Pagename'], "UTF-8") <= 40 ? $actions[$k]['Pagename'] : mb_substr($actions[$k]['Pagename'], 0, 39, "UTF-8") . '...';
            $percents = ($actions[$k]['percents'] > 0) ? '<div style="width:' . $actions[$k]['percents'] . '%; background-position:0 ' . $bkground . 'px;"><div></div></div>' :
                    '<div style="width:0%; background-position:0 ' . $bkground . 'px;"><div></div></div>';
            $required_sumF = ($actions[$k]['required_sum'] > 0) ? 'из <span>' . $actions[$k]['required_sumF'] . '</span>' : '';

            if($action['page_id'] != NULL) {
                $userString = '<div><a href="' . $actions[$k]['Pagelink'] . '" target="_blank">' . $pagename . '</a></div>';
            } else {
                $userString = '<div><a href="' . $actions[$k]['Userurl'] . '" target="_blank">' . $username . '</a></div>';
            }
            
            $jsonData['li'][] = '<div class="mini_block_outer' . $last . '">
				<div class="mini_block">
					<div class="mini_block_inner">
						<div class="mb_timer">' . $actions[$k]['dates'] . ' <span>' . $left . '</span></div>' . $toplink . $blockedlink . '
						<div class="mb_title">
							<a href="' . $config->ymoney->APP_PAGE . '#app_data=' . $actions[$k]['id'] . '" target="_top">' . $name . '</a>' . $userString .
                                                '</div>
						<div class="mb_money_line">' . $percents . '</div>
						<div class="mb_collected" id="mb_collected' . $actions[$k]['id'] . '">Собрано <span>' . $actions[$k]['current_sumF'] . '</span> ' . $required_sumF . ' <span>руб.</span></div>
<a id="mb_help' .
                    $actions[$k]['id'] . '" href="https://money.yandex.ru/direct-payment.xml?receiver=' .
                    $actions[$k]['receiver'] . '&Formcomment=' .
                    urlencode('На акцию в Вконтакте "' . $actions[$k]['name'] . '"') . '&destination=' .
                    urlencode('Для акции «'.$actions[$k]['name'].'» от пользователя vk.com/id' . $user_id . '. Чтобы отправить деньги анонимно, удалите весь комментарий.') . '&_openstat=socapp;vk;p2p;wid&label=vk_' . $actions[$k]['id'] . '" target="_blank" class="mb_help">Помочь деньгами</a>
                    <a data-id="' . $actions[$k]['id'] . '" style="display:none;" href="#" title="Рассказать" class="mb_shelp"></a>
					</div>
				</div>
			</div>';
            $i++;
            if ($i == 10) {
                $jsonData['next'] = true;
                break;
            }
        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }

    public function myactionspageAction() {
        $jsonData = array('li' => array());
        $config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        // Интерфейс админа
        $admin = $this->_adminInteface($user_id);

        // Проверяем, давал ли пользователь права доступа приложения для Яндекс.Денег
        $zayac = $this->_verifyUser($this->action_code_flag . $this->action_code_my);
        $account = $zayac->getAccountInformation();

        // акции
        $actionsModel = new Application_Model_DbTable_Action();
        $drafts = null;
        $blocked = false;
        $asPages = null;
        $onlyLink = null;
        $selected = $this->_getParam('selected');
        
        switch($selected) {
            case "pages":
                $asPages = true;
                break;
            case "hidden":
                $onlyLink = true;
                break;
            default:
                break;
        }

        $search = null;
        if ($this->_getParam('search') && $this->_getParam('search') != 'false') {
            $search = $this->_rqfs($this->_getParam('search'));
        }
        $jsonData['next'] = false;
        $page = $this->_getParam('page');
        if ($this->_getParam('next') == 'true') {
            $offset = $page * 9;
            $jsonData['prev'] = true;
        } else {
            $offset = ($page - 2) * 9;
            if ($offset == 0)
                $jsonData['prev'] = false;
            else
                $jsonData['prev'] = true;
        }

		$group = 2;
        $actions = $actionsModel->getActions($user_id, null, $drafts, null, $search, null, $blocked, null, 10, $offset, null, null, $group, $asPages, $onlyLink);
        $jsonData['actions'] = $actions;
        $li = array();
        $i = 1;
        foreach ($actions as $k => $action) {
            $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
            // Если акция завершена - меняем статус
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
            
            $this->_action2Process($actions[$k]);
//            $start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
//            $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
//            if ($action['completed'] == 'Y') {
//                $actions[$k]['left'] = 'акция завершена';
//            }
//            if (!$action['date_end'] || $action['date_end'] == '0000-00-00 00:00:00') {
//                if ($action['date_start'] != '0000-00-00 00:00:00') {
//                    $actions[$k]['dates'] = 'С ' . $this->_helper->dateFormat($action['date_start']);
//                    if (abs(mktime() - $start_stamp) >= 31536000) {
//                        $actions[$k]['dates'] .= ' ' . date('Y', $start_stamp);
//                    }
//                } else {
//                    $actions[$k]['dates'] = 'Всегда';
//                }
//            } elseif ($action['date_end'] != '0000-00-00 00:00:00') {
//                if (date('n', $start_stamp) == date('n', $end_stamp)
//                        && abs($end_stamp - $start_stamp) < 31536000
//                        && (abs(mktime() - $start_stamp) < 31536000)
//                        && (abs(mktime() - $end_stamp) < 31536000)
//                ) {
//                    $actions[$k]['dates'] = date('j', $start_stamp) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
//                } elseif (
//                        abs($end_stamp - $start_stamp) < 31536000
//                        && (abs(mktime() - $start_stamp) < 31536000)
//                        && (abs(mktime() - $end_stamp) < 31536000)
//                ) {
//                    $actions[$k]['dates'] = $this->_helper->dateFormat($action['date_start']) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
//                } else {
//                    $actions[$k]['dates'] = $this->_helper->dateFormat($action['date_start']) . ' ' . date('Y', $start_stamp) . ' &mdash; ' .
//                            $this->_helper->dateFormat($action['date_end']) . ' ' . date('Y', $end_stamp);
//                }
//                // Если акция завершена - меняем статус
//                if ($end_stamp < mktime()) {
//                    if ($action['completed'] == 'N') {
//                        $ac = $actionsModel->find($action['id'])->current();
//                        $ac->completed = 'Y';
//                        $ac->save();
//                        unset($ac);
//                        unset($actions[$k]);
//                    }
////                    continue;
//                }
//
//                if ($action['completed'] == 'Y') {
//                    $actions[$k]['left'] = 'акция завершена';
//                } else {
//                    $dateend = explode(" ", $action['date_end']);
//                    $dateend = $dateend[0];
//                    $dateend = explode("-", $dateend);
//                    $dtime = mktime(0, 0, 0, $dateend[1], $dateend[2], $dateend[0]);
//                    $ctime = mktime();
//                    $fivedays = 86400 * 5;
//                    if (($dtime - $ctime) <= $fivedays) {
//                        $dn = ceil(($dtime - $ctime) / 86400);
//                        if ($dn < 5 && $dn > 1) {
//                            $dt = " дня";
//                            $dl = " осталось ";
//                        } elseif ($dn == 1) {
//                            $dt = " день";
//                            $dl = " остался ";
//                        } elseif ($dn >= 5) {
//                            $dt = " дней";
//                            $dl = " осталось ";
//                        } else {
//                            $actions[$k]['left'] = 'акция завершена';
//                        }
//                        if (($dtime - $ctime) > 0) {
//                            $actions[$k]['left'] = $dl . ceil(($dtime - $ctime) / 86400) . $dt;
//                        }
//                    }
//                }
//            }
            if ($action['required_sum'] > 0 && $action['required_sum'] > ($action['current_sum'] + $action['all_sum'])) {
                $actions[$k]['percents'] = (($action['current_sum'] + $action['all_sum']) / $action['required_sum']) * 100;
                if ($actions[$k]['percents'] < 1 && $actions[$k]['required_sum'] <= ($actions[$k]['current_sum'] + $actions[$k]['all_sum']))
                    $actions[$k]['percents'] = 1;
            } elseif ($action['required_sum'] > 0 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum'])) {
                $actions[$k]['percents'] = 100;
            } else {
                $actions[$k]['percents'] = false;
            }
            if($action['group']==1) {
            	$usersactionModel = new Application_Model_DbTable_Usersaction();
            	$friendlist = $usersactionModel->selectfriends($action['id']);
            	$actions[$k]['friends'] = $friendlist;
            	$actions[$k]['required_sum_group'] = $action['required_sum']/(count($actions[$k]['friends'])+1)*1.005;
            } else {
            	$actions[$k]['required_sum_group'] = 0;
            }
            if (strlen($action['name']) == 0) {
                $actions[$k]['name'] = 'Без названия';
            }

            if ($action['required_sum'] > 0) {
                $actions[$k]['required_sumF'] = $this->_helper->numberFormat($action['required_sum']);
            } else {
                $actions[$k]['required_sumF'] = 0;
            }

            if ($action['current_sum'] > 0 || $action['all_sum'] > 0) {
                $actions[$k]['current_sumF'] = $this->_helper->numberFormat(($action['current_sum'] + $action['all_sum']));
            } else {
                $actions[$k]['current_sumF'] = 0;
            }

            if ($actions[$k]['percents'] >= 0 && $actions[$k]['completed'] == 'N') {
                if ($actions[$k]['percents'] == 0) {
                    $bkground = '0';
                } elseif ($actions[$k]['percents'] <= 33) {
                    $bkground = '0';
                } elseif ($actions[$k]['percents'] <= 67) {
                    $bkground = '-7';
                } else {
                    $bkground = '-14';
                }
            } else {
                $bkground = '-21';
            }
            
            $tmpName = explode(' ', $action['name']);
            $j = 0;
            $s = count($tmpName);
            $tmp1 = $tmp2 = "";
            while($j < $s) {
                if(isset($tmpName[$j]) && mb_strlen($tmp2.$tmpName[$j], 'UTF-8') > 47) {
                    $tmp1 = '...';
                    break;
                }
				$tmp2 .= $tmpName[$j].' ';
                $j++;
            }
			$actions[$k]['filteredName'] = trim($tmp2) . $tmp1;

            $last = ($i % 3) == 0 ? " last" : '';
            $left = isset($actions[$k]['left']) ? $actions[$k]['left'] : '';
            $toplink = ($actions[$k]['top'] == 'Y' && $admin) ? '<img src="/i/symbol-favi-24.png" alt="Акция в ТОПе"  class="mb_ico" />' : '';
            $blockedlink = ($actions[$k]['blocked'] == 'Y' && $admin) ? '<img src="/i/symbol-rejected-24.png" alt="Акция заблокирована администратором"  class="mb_ico" />' : '';
            $name = mb_strlen($actions[$k]['filteredName'], "UTF-8") <= 53 ? $actions[$k]['filteredName'] : mb_substr($actions[$k]['filteredName'], 0, 52, "UTF-8") . "…";
            $username = mb_strlen($actions[$k]['Username'], "UTF-8") <= 18 ? $actions[$k]['Username'] : mb_substr($actions[$k]['Firstname'], 0, 1, "UTF-8") . '. ' . $actions[$k]['Lastname'];
            $pagename = mb_strlen($actions[$k]['Pagename'], "UTF-8") <= 40 ? $actions[$k]['Pagename'] : mb_substr($actions[$k]['Pagename'], 0, 39, "UTF-8") . '...';
            $percents = ($actions[$k]['percents'] > 0) ? '<div style="width:' . $actions[$k]['percents'] . '%; background-position:0 ' . $bkground . 'px;"><div></div></div>' :
                    '<div style="width:0%; background-position:0 ' . $bkground . 'px;"><div></div></div>';
            $required_sumF = ($actions[$k]['required_sum'] > 0) ? 'из <span>' . $actions[$k]['required_sumF'] . '</span>' : '';
            
            if($action['page_id'] != NULL) {
                $userString = '<div><a href="' . $actions[$k]['Pagelink'] . '" target="_blank">' . $pagename . '</a></div>';
            } else {
                $userString = '<div><a href="' . $actions[$k]['Userurl'] . '" target="_blank">' . $username . '</a></div>';
            }
            
            $jsonData['li'][] = '<div class="mini_block_outer' . $last . '">
				<div class="mini_block">
					<div class="mini_block_inner">
						<div class="mb_timer">' . $actions[$k]['dates'] . ' <span>' . $left . '</span></div>' . $toplink . $blockedlink . '
						<div class="mb_title">
							<a href="' . $config->ymoney->APP_PAGE . '#app_data=' . $actions[$k]['id'] . '" target="_top">' . $name . '</a>' . $userString .
                                                '</div>
						<div class="mb_money_line">' . $percents . '</div>
						<div class="mb_collected" id="mb_collected' . $actions[$k]['id'] . '">Собрано <span>' . $actions[$k]['current_sumF'] . '</span> ' . $required_sumF . ' <span>руб.</span></div>
<a id="mb_help' .
                    $actions[$k]['id'] . '" href="https://money.yandex.ru/direct-payment.xml?receiver=' .
                    $actions[$k]['receiver'] . '&Formcomment=' .
                    urlencode('На акцию в Вконтакте "' . $actions[$k]['name'] . '"') . '&destination=' .
                    urlencode('Для акции «'.$actions[$k]['name'].'» от пользователя vk.com/id' . $user_id . '. Чтобы отправить деньги анонимно, удалите весь комментарий.') . '&_openstat=socapp;vk;p2p;wid&label=vk_' . $actions[$k]['id'] . '" target="_blank" class="mb_help">Помочь деньгами</a>
                    <a data-id="' . $actions[$k]['id'] . '" style="display:none;" href="#" title="Рассказать" class="mb_shelp"></a>
					</div>
				</div>
			</div>';
            $i++;
            if ($i == 10) {
                $jsonData['next'] = true;
                break;
            }
        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }

    public function accountAction() {
        $this->initFB($this->action_code_flag . $this->action_code_account);
        $config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $user_id = $user_id = $ymoneyNamespace->vk['viewer_id'];
        $this->view->cancreate = $this->cancreate($user_id);
        $this->view->appurl = $config->ymoney->APP_PAGE;
        $this->view->admin = $this->_adminInteface($user_id);
        if (!$this->saveuser()) {
		echo "Произошла ошибка";
            //$this->_redirect($this->_helper->url('index', 'actions', 'default'));
            exit;
        }

        $this->view->headTitle($config->ymoney->APP_NAME);
        $this->view->headMeta($config->ymoney->description2, 'description');

        $this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
        $this->view->headMeta()->setProperty('vk:title', $config->ymoney->APP_NAME);
        $this->view->headMeta()->setProperty('vk:type', "website");
        $this->view->headMeta()->setProperty('vk:url', $config->ymoney->APP_PAGE_ORIGIN);
        $this->view->headMeta()->setProperty('vk:app_id', $config->ymoney->APP_ID);
        $this->view->headMeta()->setProperty('vk:image', $config->ymoney->host . '/i/app_75.jpg');
        $this->view->headMeta()->setProperty('vk:description', $config->ymoney->description2);

        // Проверяем, давал ли пользователь права доступа приложения для Яндекс.Денег
        $zayac = $this->_verifyUser($this->action_code_flag . $this->action_code_account);
        $account = $zayac->getAccountInformation();

        $this->view->account = $account['account'];
        $this->view->balance = $account['balance'] . ' руб.';
        /*
          $user_profile = $facebook->api('/'.$user_id.'/','GET');
          if($user_profile) {
          $this->view->email = $user_profile['email'];
          } else {
          $this->view->error = 'Нет доступа к информации о пользователе';
          }
         */
    }

    public function createAction() {
        // Устанавливаем приложение
        //echo "Пришли отсюда: ".$_SERVER['http_referer']."<br>";
        //echo "Устанавливаем приложение...".$this->action_code_flag.$this->action_code_create."<br>";
        $this->initFB($this->action_code_flag . $this->action_code_create);
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        //echo "appConfig...<br>"; //exit;
        if (!$this->saveuser()) {
			echo "Произошла ошибка";
            //$this->_redirect($this->_helper->url('index', 'actions', 'default'));
            exit;
        }
        $this->view->action = array();

        $this->view->appurl = $config->ymoney->APP_PAGE;
        $this->view->hostUrl = $config->ymoney->host;
        $script = "var getphotoAjaxUrl = '{$this->_helper->url('getphoto', 'actions', 'default')}';\n";
        $script .= "var checknameAjaxUrl = '{$this->_helper->url('checkname', 'actions', 'default')}';\n";
        $script .= "var fileCounter=0;";
        $this->view->headScript()->prependScript($script, $type = 'text/javascript');

        $this->view->headTitle($config->ymoney->APP_NAME);
        $this->view->headMeta($config->ymoney->description2, 'description');

        $this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
        $this->view->headMeta()->setProperty('vk:title', $config->ymoney->APP_NAME);
        $this->view->headMeta()->setProperty('vk:type', "website");
        $this->view->headMeta()->setProperty('vk:url', $config->ymoney->APP_PAGE_ORIGIN);
        $this->view->headMeta()->setProperty('vk:app_id', $config->ymoney->APP_ID);
        $this->view->headMeta()->setProperty('vk:image', $config->ymoney->host . '/i/app_75.jpg');
        $this->view->headMeta()->setProperty('vk:description', $config->ymoney->description2);

        // Проверяем, давал ли пользователь права доступа приложения для Яндекс.Денег
        //echo "Проверяем, давал ли пользователь права доступа приложения для Яндекс.Денег<br/>"; //exit;
        $zayac = $this->_verifyUser($this->action_code_flag . $this->action_code_create);
        
        //var_dump($zayac); exit;
        $account = $zayac->getAccountInformation();
        //var_dump($account); exit;
        $this->view->balance = round($account['balance']);
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $this->view->admin = $this->_adminInteface($user_id);

        $userModel = new Application_Model_DbTable_Users();
        $user = $userModel->find($user_id)->current();
        $this->view->user = array('Userurl' => $user->url, 'Username' => $user->name, 'Firstname' => $user->firstname, 'Lastname' => $user->lastname);

        $actionsModel = new Application_Model_DbTable_Action();
        $actionsModel->setToken($user_id, $ymoneyNamespace->access_token, $account['account']);

        // Обработка формы
        if ($this->_request->isPost()) {
            $params = $this->_getAllParams();
//            var_dump($params['who']);
//            var_dump($user_id);
//            die();
            if(isset($params['who']) && $params['who'] != $user_id) {
                $who = $params['who'];
                $pagesModel = new Application_Model_DbTable_Pages();
                $select = $pagesModel->select()->where('page_id=?', $params['who']);
                $authorArray = $pagesModel->fetchAll($select)->toArray();
                if(count($authorArray) == 0) {
                    $fields = array(
                        'id' => NULL,
                        'page_id' => $params['who'],
                        'name' => $params['actionnameh'],
                        'url' => 'http://vk.com/club' . $params['who'],
                    );
                    $tmp = $pagesModel->insert($fields);
                    if($tmp > 0) {
                        $params['who'] = $tmp;
                    }
                } else {
                    $params['who'] = $authorArray[0]['id'];
                }
            } else {
                unset($params['who']);
            }
            $saveresult = $this->_save($params, $account['account'], $account['balance']);
            if (is_int($saveresult) && $saveresult > 0) {
                $arr = array('who' => (isset($params['who']) && !empty($params['who'])) ? $who : $user_id, 'newId' => $saveresult);
                $this->_helper->redirector('my', 'actions', 'default', $arr);
            }
        }
        
        $this->view->uid = $ymoneyNamespace->vk['viewer_id'];
        $this->view->action['id'] = 0;
    }

    public function imageAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $path1 = $this->_getParam('src');
        if ($path1)
            Application_Model_DbTable_Photo::ImageShowJPG($path1, 109, 84);
    }

    private function _save($params, $account, $balance, $way = 'create') {
        //echo "Сохраняем акцию...";
        $result = false;
        $config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $actionsModel = new Application_Model_DbTable_Action();
        $usersactionModel = new Application_Model_DbTable_Usersaction();
        $photoModel = new Application_Model_DbTable_Photo();

        if (!isset($params['date_start']) || strlen($params['date_start'] == 0)) {
            $date_start = '';
        } else
            $date_start = $params['date_start'];
        if (!isset($params['date_end']) || strlen($params['date_end'] == 0) || $params['date_end'] == 0) {
            $date_end = '';
            if ($way == 'edit') {
                $act = $actionsModel->find($params['action_id'])->current();
                if ($act) {
                    if ($act->completed == 'Y') {
                        $act->completed = 'N';
                        $act->save();
                    }
                }
            }
        } else {
            $date_end = $params['date_end'];
            if ($this->_helper->dateFormat($date_end, null, true) >= $this->_helper->dateFormat($date_start, null, true)) {
                if ($way == 'edit') {
                    $act = $actionsModel->find($params['action_id'])->current();
                    if ($act) {
                        if ($act->completed == 'Y') {
                            $act->completed = 'N';
                            $act->save();
                        }
                    }
                }
            }
        }

        $params['description'] = stripslashes(strip_tags($params['description']));
        $params['description'] = str_replace("'", "", $params['description']);
        $params['description'] = str_replace('"', '', $params['description']);
        //echo $params['current_sum'];
        $params['required_sum'] = str_replace(',', '.', $params['required_sum']);
        $params['required_sum'] = str_replace(' ', '', $params['required_sum']);
        $params['current_sum'] = str_replace(',', '.', $params['current_sum']);
        $params['current_sum'] = str_replace(' ', '', $params['current_sum']);



        $params['name'] = stripslashes(strip_tags($params['name']));
        $params['name'] = str_replace("'", "", $params['name']);
        $params['name'] = str_replace('"', '', $params['name']);

        $params['tags'] = stripslashes(strip_tags($params['tags']));
        $params['tags'] = str_replace("'", "", $params['tags']);
        $params['tags'] = str_replace('"', '', $params['tags']);

        $params['video'] = strip_tags($params['video']);
        $params['video'] = str_replace("'", "", $params['video']);
        $params['video'] = str_replace('"', '', $params['video']);

        if ($params['url'] != 'http://' && strlen($params['url'])) {
            $params['url'] = strip_tags($params['url']);
            $params['url'] = str_replace("'", "", $params['url']);
            $params['url'] = str_replace('"', '', $params['url']);
            $params['url'] = str_replace("\\", '', $params['url']);
        } else {
            $params['url'] = '';
        }

        if(!isset($params['group'])) {
        	$params['group'] = 0;
        }
        
        switch ($params['source']) {
            case 0: $params['source'] = 'ym';
                break;
            //case 1: $params['source'] = 'fromdate';
                break;
            case 1: case 2: $params['source'] = 'fromapp';
                break;
            default: $params['source'] = 'ym';
        }

        $today = date('Y-m-d H:i:s');

        if(!isset($params['ymfromdate'])) $params['ymfromdate'] == '';
        if(!isset($params['who'])) $params['who'] == '';
        if(!isset($params['only_link'])) $params['only_link'] == '';
        if($params['ymfromdate'] == '' && $params['source'] == 'fromdate') {
            $params['ymfromdate'] = date('Y-m-d');
        }

        if (!$result) {
            $fields = array(
                'date_created' => $today,
                'date_modified' => $today,
                'user_id' => $user_id,
                'name' => $params['name'],
                'description' => $params['description'],
                'tags' => $params['tags'],
                'date_start' => $date_start,
                'date_end' => $date_end,
                'url' => $params['url'],
                'video' => $params['video'],
                'source' => $params['source'],
                'ymfromdate' => $params['ymfromdate'],
                'required_sum' => $params['required_sum'],
                'current_sum' => $params['current_sum'],
                'draft' => $params['draft'],
            	'group' => $params['group'],
                'receiver' => $account,
                'token' => $ymoneyNamespace->access_token
            );
            if($params['who']) {
                $fields['page_id'] = $params['who'];
            } else {
                $fields['page_id'] = NULL;
            }
            if($params['only_link'] == "on") {
                $fields['only_link'] = "Y";
            } else {
                $fields['only_link'] = "N";
            }
            $photoModel = new Application_Model_DbTable_Photo();
            if ($way == 'edit') {
                // Сохранение редактирования
                unset($fields['date_created']);
                unset($fields['receiver']);
                if($this->_adminInteface($user_id)) {
                    unset($fields['user_id']);
                    unset($fields['token']);
                }
                $newAction = array();
                $photoModel->deleteByAction($params['action_id']);
                $newAction['id'] = $params['action_id'];
                $saveFlag = $actionsModel->update($fields, 'id = ' . $params['action_id']);
            } else {
                // Сохранение новой акции
                if ($params['source'] == 'ym') {
                    $fields['all_sum'] = $balance;
                }
                $newAction = $actionsModel->createRow($fields);
                $saveFlag = $newAction->save();
            }

            if ($saveFlag) {
                $result = intval($newAction['id']);
                if($params['group']==1 && count($params['friends'])>0) {
                	$usersactionModel->delete('action_id = '.$result);
                	$summ = round($params['required_sum']/count($params['friends']));
                	$usersactionModel->insertgroup($params['friends'], $result, $summ);
                }

                if (isset($params['photo']) && count($params['photo']) > 0) {
                    $photos = $params['photo'];
                    foreach ($photos as $photo) {
                        $fields = array(
                            'action_id' => $result,
                            'pid' => $photo
                        );
                        $newphoto = $photoModel->createRow($fields);
                        $newphoto->save();
                    }
                }
            }
        } else {
            echo $result;
        }
        return $result;
    }

    private function _openWindow($act) {
        $config = Zend_Registry::get('appConfig');
        //echo "top.location.href='". $config->ymoney->host.$this->_helper->url('request-org', 'actions', 'default')."?act=".$act."';"; exit;
        echo "<script>top.location.href='" . $config->ymoney->host . $this->_helper->url('request-org', 'actions', 'default') . "?act=" . $act . "';</script>";
    }

    public function requestOrgAction() {
        $config = Zend_Registry::get('appConfig');
        $scope = 'operation-history account-info operation-details';
        ZenYandexClient::setClientId($config->ymoney->client_id);
        if ($this->_getParam('act')) {
            $act = $this->removeCRLFact('?act=' . $this->_getParam('act'));
        } else {
            $act = '';
        }
        //echo "requestOrgAction<br>";
        //echo "client_id ".$config->ymoney->client_id."<br>";
        //echo "url ".$config->ymoney->host . $config->ymoney->redirect_uri . $act."<br>"; exit;
        ZenYandexClient::authorize($scope, $config->ymoney->host . $config->ymoney->redirect_uri . $act);
    }

    public function callbackAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');

        if ($this->_getParam('error') && $this->_getParam('error') == 'access_denied') {
            $this->_redirect($config->ymoney->APP_PAGE);
            exit;
        }

        ZenYandexClient::setClientId($config->ymoney->client_id);
        ZenYandexClient::setClientSecret($config->ymoney->client_secret);
        $ymoneyNamespace->access_token = ZenYandexClient::convertAuthToken();

        if ($this->_getParam('act')) {
            $act = $this->removeCRLFact('#app_data=' . $this->_getParam('act'));
        } else {
            $act = '';
        }
        //echo "callbackAction ".$ymoneyNamespace->access_token; exit;
        //$newurl = "http://" . $_SERVER['SERVER_NAME'] . $this->_helper->url($act, 'actions', 'default');
        $this->_redirect($config->ymoney->APP_PAGE . $act);
        exit;
        //echo "newurl ".$newurl; exit;
//	    $script = "window.opener.location.href = '{$newurl}'\n";
//	    $script .= "window.close();\n";
//        $this->view->headScript()->prependScript($script, $type = 'text/javascript');
    }

    public function stopAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $jsonData = false;
        $user_id = $ymoneyNamespace->vk['viewer_id'];

        $action_id = $this->removeCRLF($this->_getParam('action_id'));

        if (strlen($user_id) > 3) {
            if ($action_id > 0) {
                $id = intval($action_id);
                $actionsModel = new Application_Model_DbTable_Action();
                $action = $actionsModel->find($id)->current();
                if ($action) {
                    if ($user_id == $action->user_id || $this->_adminInteface($user_id)) {
                        $action->completed = 'Y';
                        if ($action->save()) {
                            $jsonData = true;
                        }
                    }
                }
            }
        }
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }

    public function startAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $jsonData = false;
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $action_id = $this->removeCRLF($this->_getParam('action_id'));
        if (strlen($user_id) > 3) {
            if ($action_id > 0) {
                $id = intval($action_id);
                $actionsModel = new Application_Model_DbTable_Action();
                $action = $actionsModel->find($id)->current();
                if ($action) {
                    if ($user_id == $action->user_id || $this->_adminInteface($user_id)) {
                        $action->completed = 'N';
                        $action->date_end = '0000-00-00';
                        if ($action->save()) {
                            $jsonData = true;
                        }
                    }
                }
            }
        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }

    public function topAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $jsonData = false;
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $action_id = $this->removeCRLF($this->_getParam('action_id'));
        if ($this->_adminInteface($user_id)) {
            if ($action_id > 0) {
                $id = intval($action_id);
                $actionsModel = new Application_Model_DbTable_Action();
                $action = $actionsModel->find($id)->current();
                if ($action) {
                    $action->top = 'Y';
                    if ($action->save()) {
                        $jsonData = true;
                    }
                }
            }
        }
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }

    public function untopAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $jsonData = false;
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $action_id = $this->removeCRLF($this->_getParam('action_id'));
        if ($this->_adminInteface($user_id)) {
            if ($action_id > 0) {
                $id = intval($action_id);
                $actionsModel = new Application_Model_DbTable_Action();
                $action = $actionsModel->find($id)->current();
                if ($action) {
                    $action->top = 'N';
                    if ($action->save()) {
                        $jsonData = true;
                    }
                }
            }
        }
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }
    
    public function nomainAction() {
    	$ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
    	$config = Zend_Registry::get('appConfig');
    	$jsonData = false;
    	$user_id = $ymoneyNamespace->vk['viewer_id'];
    	$action_id = $this->removeCRLF($this->_getParam('action_id'));
    	if ($this->_adminInteface($user_id)) {
    		if ($action_id > 0) {
    			$id = intval($action_id);
    			$actionsModel = new Application_Model_DbTable_Action();
    			$action = $actionsModel->find($id)->current();
    			if ($action) {
    				$action->nomain = 1;
    				if ($action->save()) {
    					$jsonData = true;
    				}
    			}
    		}
    	}
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	header('Content-type: application/json; charset=utf-8');
    	echo Zend_Json::encode($jsonData);
    }
    
    public function onmainAction() {
    	$ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
    	$config = Zend_Registry::get('appConfig');
    	$jsonData = false;
    	$user_id = $ymoneyNamespace->vk['viewer_id'];
    	$action_id = $this->removeCRLF($this->_getParam('action_id'));
    	if ($this->_adminInteface($user_id)) {
    		if ($action_id > 0) {
    			$id = intval($action_id);
    			$actionsModel = new Application_Model_DbTable_Action();
    			$action = $actionsModel->find($id)->current();
    			if ($action) {
    				$action->nomain = 0;
    				if ($action->save()) {
    					$jsonData = true;
    				}
    			}
    		}
    	}
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	header('Content-type: application/json; charset=utf-8');
    	echo Zend_Json::encode($jsonData);
    }

    public function hideAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $jsonData = false;
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $action_id = $this->removeCRLF($this->_getParam('action_id'));
        if ($this->_adminInteface($user_id)) {
            if ($action_id > 0) {
                $id = intval($action_id);
                $actionsModel = new Application_Model_DbTable_Action();
                $action = $actionsModel->find($id)->current();
                if ($action) {
                    $action->hidden = 'Y';
                    if ($action->save()) {
                        $jsonData = true;
                    }
                }
            }
        }
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }

    public function unhideAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $jsonData = false;
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $action_id = $this->removeCRLF($this->_getParam('action_id'));
        if ($this->_adminInteface($user_id)) {
            if ($action_id > 0) {
                $id = intval($action_id);
                $actionsModel = new Application_Model_DbTable_Action();
                $action = $actionsModel->find($id)->current();
                if ($action) {
                    $action->hidden = 'N';
                    if ($action->save()) {
                        $jsonData = true;
                    }
                }
            }
        }
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }

    public function blockAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $jsonData = false;
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $action_id = $this->removeCRLF($this->_getParam('action_id'));
        if ($this->_adminInteface($user_id)) {
            if ($action_id > 0) {
                $id = intval($action_id);
                $actionsModel = new Application_Model_DbTable_Action();
                $action = $actionsModel->find($id)->current();
                if ($action) {
                    $action->blocked = 'Y';
                    if ($action->save()) {
                        $jsonData = true;
                    }
                }
            }
        }
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }

    public function unblockAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $jsonData = false;
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $action_id = $this->removeCRLF($this->_getParam('action_id'));
        if ($this->_adminInteface($user_id)) {
            if ($action_id > 0) {
                $id = intval($action_id);
                $actionsModel = new Application_Model_DbTable_Action();
                $action = $actionsModel->find($id)->current();
                if ($action) {
                    $action->blocked = 'N';
                    if ($action->save()) {
                        $jsonData = true;
                    }
                }
            }
        }
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }

    public function editAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $this->initFB();
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $this->view->appurl = $config->ymoney->APP_PAGE;
        $this->view->hostUrl = $config->ymoney->host;
        $this->view->admin = $this->_adminInteface($user_id);
        $script = "var getphotoAjaxUrl = '{$this->_helper->url('getphoto', 'actions', 'default')}';\n";
        $script .= "var checknameAjaxUrl = '{$this->_helper->url('checkname', 'actions', 'default')}';\n";
        $error = true;
        
        $this->view->headTitle($config->ymoney->APP_NAME);
        $this->view->headMeta($config->ymoney->description2, 'description');

        $this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
        $this->view->headMeta()->setProperty('vk:title', $config->ymoney->APP_NAME);
        $this->view->headMeta()->setProperty('vk:type', "website");
        $this->view->headMeta()->setProperty('vk:url', $config->ymoney->APP_PAGE_ORIGIN);
        $this->view->headMeta()->setProperty('vk:app_id', $config->ymoney->APP_ID);
        $this->view->headMeta()->setProperty('vk:image', $config->ymoney->host . '/i/app_75.jpg');
        $this->view->headMeta()->setProperty('vk:description', $config->ymoney->description2);

        // Проверяем, давал ли пользователь права доступа приложения для Яндекс.Денег
        $zayac = $this->_verifyUser($this->action_code_flag . $this->action_code_edit . '-' . $this->_getParam('action_id'));
        $account = $zayac->getAccountInformation();
        $action_id = $this->removeCRLF($this->_getParam('action_id'));
        if (strlen($user_id) > 3) {
            if ($action_id > 0) {
                $id = intval($action_id);
                $actionsModel = new Application_Model_DbTable_Action();
                $actionsModel->setToken($user_id, $ymoneyNamespace->access_token, $account['account']);
                $action = $actionsModel->find($id)->current();
                if ($action['id']) {
                    $action = $actionsModel->getAction($action['id']);
                    if ($user_id == $action['user_id'] || $this->_adminInteface($user_id)) {
                        $error = false;
                        $this->view->adminEdit = 0;
                        
                        $pagesModel = new Application_Model_DbTable_Pages();
                        $userModel = new Application_Model_DbTable_Users();
                        if($user_id != $action['user_id']) {
                            $select = $pagesModel->select()->where('user_id=?', $action['user_id']);
                            $this->view->adminPages = $pagesModel->fetchAll($select)->toArray();
                            $this->view->adminEdit = 1;
                            $actionUser = $userModel->find($action['user_id'])->current();
                            $this->view->actionUser = $actionUser;
                        }
                        if($action['page_id'] != NULL) {
                            $select = $pagesModel->select()->where('id=?', $action['page_id']);
                            $author = $pagesModel->fetchRow($select);                                
                            
                            $this->view->pages = $author;
                        }
                        
                        $user = $userModel->find($user_id)->current();
                        $this->view->user = array('Userurl' => $user->url, 'Username' => $user->name, 'Firstname' => $user->firstname, 'Lastname' => $user->lastname);
                        
                        // Обработка формы
                        if ($this->_request->isPost()) {
                            $params = $this->_getAllParams();
                            $cur_user_id = $user_id != $action['user_id'] ? $action['user_id'] : $user_id; 
                            if(isset($params['who']) && $params['who'] != $cur_user_id /*|| $params['who'] != $action['user_id']*/) {
                                $select = $pagesModel->select()->where('page_id=?', $params['who']); 
                                $author = $pagesModel->fetchRow($select);//var_dump($author); exit;
                                if($author != NULL) {
                                    $params['who'] = $author->id;
                                } else {
                                    $fields = array(
                                        'id' => NULL,
                                        'page_id' => $params['who'],
                                        'name' => $params['actionnameh'],
                                        'url' => 'http://vk.com/club' . $params['who'],
                                    );
                                    $tmp = $pagesModel->insert($fields);
                                    if($tmp > 0) {
                                        $params['who'] = $tmp;
                                    }
//                                    unset($params['who']);
                                }
                            } elseif(isset($params['who'])) {
                                unset($params['who']);
                            }
                            //echo "Обработка формы...<br>";
                            $saveresult = $this->_save($params, $account['account'], $account['balance'], 'edit');
                            if (is_int($saveresult) && $saveresult > 0) {
                                $this->_redirect($this->_helper->url('index', 'index', 'default') . '?app_data=' . $saveresult);
                            }
                        }
                        switch ($action['source']) {
                            case 'ym': $action['source'] = 0;
                                break;
//                            case 'fromdate': $action['source'] = 1; break;
                            case 'fromdate': case 'fromapp': $action['source'] = 1;
                                break;
                            default: $action['source'] = 0;
                        }

                        $this->view->balance = $action['all_sum'];
                        $stat_from = ($action['date_start'] != '0000-00-00 00:00:00' ? $action['date_start'] : date('Y-m-d 00:00:00'));
                        $stat_to = ($action['date_end'] != '0000-00-00 00:00:00' ? $action['date_end'] : 0);

                        if ($stat_to == 0) {
                            $this->view->endhidden = 0;
                            $this->view->endhiddenstamp = 0;
                        } else {
                            $this->view->endhidden = $this->_helper->dateFormat($action['date_end'], null, null, true);
                            $this->view->endhiddenstamp = $this->_helper->dateFormat($action['date_end'], null, true);
                        }

                        $this->view->starthidden = $this->_helper->dateFormat($action['date_start'], null, null, true);
                        $this->view->starthiddenstamp = $this->_helper->dateFormat($action['date_start'], null, true);

                        $ymd = $action['ymfromdate'] != '0000-00-00 00:00:00' ? $action['ymfromdate'] : date('Y-m-d');

                        $this->view->ymfromdatehidden = $this->_helper->dateFormat($ymd, null, null, true);
                        $this->view->ymfromdatehiddenstamp = $this->_helper->dateFormat($ymd, null, true);
                        
                        $this->_action2Process($action);
//                        $start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
//                        $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
//
//                        if (!$action['date_end'] || $action['date_end'] == '0000-00-00 00:00:00') {
//                            if ($action['date_start'] != '0000-00-00 00:00:00') {
//                                $action['dates'] = 'С ' . $this->_helper->dateFormat($action['date_start']);
//                                if (abs(mktime() - $start_stamp) >= 31536000) {
//                                    $action['dates'] .= ' ' . date('Y', $start_stamp);
//                                }
//                            } else {
//                                $action['dates'] = 'Всегда';
//                            }
//                        } elseif ($action['date_end'] != '0000-00-00 00:00:00') {
//                            // Месяц и год совпадают (+/- 1)
//                            if (
//                                    date('n', $start_stamp) == date('n', $end_stamp)
//                                    &&
//                                    abs($end_stamp - $start_stamp) < 31536000 &&
//                                    (abs(mktime() - $start_stamp) < 31536000) &&
//                                    (abs(mktime() - $end_stamp) < 31536000)
//                            ) {
//                                $action['dates'] = date('j', $start_stamp) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
//                            }
//                            // Месяцы не совпадают, годы совпадают (+/- 1)
//                            elseif (
//                                    abs($end_stamp - $start_stamp) < 31536000 &&
//                                    (abs(mktime() - $start_stamp) < 31536000) &&
//                                    (abs(mktime() - $end_stamp) < 31536000)
//                            ) {
//                                $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
//                            } else {
//                                $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' ' . date('Y', $start_stamp) . ' &mdash; ' .
//                                        $this->_helper->dateFormat($action['date_end']) . ' ' . date('Y', $end_stamp);
//                            }
//
//                            $datestart = explode(' ', $action['date_start']);
//                            $action['date_start'] = $datestart[0];
//                            $dateend = explode(' ', $action['date_end']);
//                            $action['date_end'] = $dateend[0];
//
//                            if ($action['completed'] == 'Y') {
//                                $action['left'] = 'акция завершена';
//                            } else {
//
//                                $dateend = explode(" ", $action['date_end']);
//                                $dateend = $dateend[0];
//                                $dateend = explode("-", $dateend);
//                                $dtime = mktime(0, 0, 0, $dateend[1], $dateend[2], $dateend[0]);
//                                $ctime = mktime();
//                                $fivedays = 86400 * 5;
//                                if (($dtime - $ctime) <= $fivedays) {
//                                    $dn = ceil(($dtime - $ctime) / 86400);
//                                    if ($dn < 5 && $dn > 1) {
//                                        $dt = " дня";
//                                        $dl = " осталось ";
//                                    } elseif ($dn == 1) {
//                                        $dt = " день";
//                                        $dl = " остался ";
//                                    } elseif ($dn >= 5) {
//                                        $dt = " дней";
//                                        $dl = " осталось ";
//                                    } else {
//                                        $action['left'] = 'акция завершена';
//                                    }
//                                    if (($dtime - $ctime) > 0) {
//                                        $action['left'] = $dl . ceil(($dtime - $ctime) / 86400) . $dt;
//                                    }
//                                }
//                            }
//                        }

                        if ($action['required_sum'] > 0 && $action['required_sum'] > ($action['current_sum'] + $action['all_sum'])) {
                            $action['percents'] = (($action['current_sum'] + $action['all_sum']) / $action['required_sum']) * 100;
                            if ($action['percents'] < 1 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum']))
                                $action['percents'] = 1;
                        } elseif ($action['required_sum'] > 0 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum'])) {
                            $action['percents'] = 100;
                        } else {
                            $action['percents'] = false;
                        }

                        if ($action['required_sum'] > 0) {
                            $action['required_sumF'] = $this->_helper->numberFormat($action['required_sum']);
                        } else {
                            $action['required_sumF'] = 0;
                            $action['required_sum'] = 0;
                        }
                        if ($action['current_sum'] > 0 || $action['all_sum'] > 0) {
                            $action['current_sumF'] = $this->_helper->numberFormat(($action['current_sum'] + $action['all_sum']), true);
                        } else {
                            $action['current_sumF'] = 0;
                        }

                        $ymfromdate = explode(' ', $action['ymfromdate']);
                        $action['ymfromdate'] = $ymfromdate[0];
                        $photoModel = new Application_Model_DbTable_Photo();
                        $this->view->showphotos = array();

                        $this->view->selectedphotos = array();
                        $this->view->selectedphotos = $photoModel->getPhotosByAction($action['id']);
                        $fileCounter = count($this->view->selectedphotos);
                        $photos = array();
                        if (count($this->view->selectedphotos) > 0) {
                            $photosIds = implode(',', $this->view->selectedphotos);
                            $albums = $this->VK->api('photos.getAlbums', array(
                                'uid' => ($this->view->adminEdit == 1) ? $action['user_id'] : $user_id 
                                    ));
                            foreach ($albums['response'] as $album) {
                                $result = $this->VK->api('photos.get', array(
                                    //'access_token' => $ymoneyNamespace->vk['access_token'],
                                    'uid' => ($this->view->adminEdit == 1) ? $action['user_id'] : $user_id,
                                    'aid' => $album['aid'],
                                    'pids' => $photosIds
                                        ));
                                if (isset($result['response'])) {
                                    $photos = array_merge($photos, $result['response']);
                                }
                            }
                        }
                        $this->view->showphotos = $photos;
                        
                        if($action['group']==1) {
                        	$usersactionModel = new Application_Model_DbTable_Usersaction();
                        	$friendlist = $usersactionModel->selectfriends($action['id']);
                        	$action['friends'] = $friendlist;
                        	$action['required_sum_group'] = round($action['required_sum']/(count($action['friends'])+1), 2);
                        } else {
                        	$action['required_sum_group'] = 0;
                        }
                        
                        $this->view->uid = $user_id;
                        $this->view->action = $action;
                        $tmpName = explode(' ', $this->view->action['name']);
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
                        $this->view->action['filteredName'] = implode(' ', $tmpName) . $tmp1;
                    
                        $userModel = new Application_Model_DbTable_Users();
                        if($user_id == $action['user_id']) {
                            $user = $userModel->find($user_id)->current();
                        } else {
                            $user = $userModel->find($action['user_id'])->current();
                        }
                        $this->view->user = array('Userurl' => $user->url, 'Username' => $user->name, 'Uid' => $user['id']);
                    }
                }
                $script .= "var fileCounter=" . $fileCounter . ";";
                $this->view->headScript()->prependScript($script, $type = 'text/javascript');
            }
        }
        if ($error) {
            $this->_redirect($this->_helper->url('my', 'actions', 'default'));
        }
    }

    public function getphotoAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $jsonData = array();

        $action_user = $this->_getParam('action_user');
        if($action_user) {
            $user_id = $action_user;
        } else {
            $user_id = $ymoneyNamespace->vk['viewer_id'];
        }

        if (strlen($user_id) > 3) {

            $result = $this->VK->api('photos.getAlbums', array(
                //'access_token' => $ymoneyNamespace->vk['access_token'],
                'uid' => $user_id,
                    ));
            if ($result['response']) {

                $photos = array();
                foreach ($result['response'] as $value) {

                    $result = $this->VK->api('photos.get', array(
                        //'access_token' => $ymoneyNamespace->vk['access_token'],
                        'uid' => $user_id,
                        'aid' => $value['aid']
                    ));
                    if (isset($result['response'])) {
                        $photos = array_merge($photos, $result['response']);
                    }
                }
                $jsonData = $photos;
            }
        }

        foreach ($jsonData as $k => $photo) {
            $jsonData[$k]['selected'] = 0;
        }

        if ($this->_getParam('action_id')) {
            $action = $this->removeCRLF($this->_getParam('action_id'));
            $photoModel = new Application_Model_DbTable_Photo();
            $selectedphotos = array();
            $selectedphotos = $photoModel->getPhotosByAction($action);
            foreach ($jsonData as $k => $photo) {
                if (in_array($photo['pid'], $selectedphotos)) {
                    $jsonData[$k]['selected'] = 1;
                }
            }
        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }

    public function unsetAction() {
        $jsonData = false;

        $this->initFB('account');
        $config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $user_id = $ymoneyNamespace->vk['viewer_id'];

        if (!$this->saveuser()) {
            echo Zend_Json::encode($jsonData);
            exit;
        }

        // Проверяем, давал ли пользователь права доступа приложения для Яндекс.Денег
        $zayac = $this->_verifyUser($this->action_code_flag, $this->action_code_account);

        $response = $zayac->api('revoke');
        $jsonData = true;

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }

    private function _verifyUser($act) {
        $zayac = false;
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $actionModel = new Application_Model_DbTable_Action();
        $usertoken = $actionModel->getTokenByUser($user_id);

        //echo "Проверяем, давал ли пользователь $user_id права доступа приложения для Яндекс.Денег: ".$ymoneyNamespace->access_token.' = '.$usertoken."<br>"; //exit;
        // Проверяем, давал ли пользователь права доступа приложения для Яндекс.Денег
        if (!isset($ymoneyNamespace->access_token) && !$usertoken) {
            $this->_openWindow($act);
            exit;
        } elseif ($usertoken || isset($ymoneyNamespace->access_token)) {
            if (!isset($ymoneyNamespace->access_token)) {
                $ymoneyNamespace->access_token = $usertoken;
            }
            $zayac = new ZenYandexClient($ymoneyNamespace->access_token);
            $account = $zayac->getAccountInformation();
            //var_dump($account); //exit;
            if (is_string($account)) {
                //echo "this->_openWindow($act)"; exit;
                $this->_openWindow($act);
                exit;
            }
            //echo "Доступ есть<br>"; //exit;
        } else {
            $this->_openWindow($act);
            exit;
        }
        //var_dump($zayac); exit;
        return $zayac;
    }

    private function _adminInteface($user_id) {
        //echo "user_id = ".$user_id." ";
        if ($user_id) {
            $userModel = new Application_Model_DbTable_Users();
            $user = $userModel->find($user_id)->current();
            if ($user && $user->admin == 'Y') {
                return true;
            }
        }
        return false;
    }

    public function statAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $user_id = false;
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        // Интерфейс админа
        $this->view->admin = $this->_adminInteface($user_id);
        $this->view->cancreate = $this->cancreate($user_id);
        $this->view->host = $config->ymoney->host;
        $this->view->appurl = $config->ymoney->APP_PAGE;
        $this->view->appid = $config->ymoney->APP_ID;
        $script = "var stopAjaxUrl = '{$this->_helper->url('stop', 'actions', 'default')}';\n";
        $script .= "var startAjaxUrl = '{$this->_helper->url('start', 'actions', 'default')}';\n";
        $script .= "var pageAjaxUrl = '{$this->_helper->url('statpage', 'actions', 'default')}';\n";
        $script .= "var getfriendsAjaxUrl = '{$this->_helper->url('getfriends', 'index', 'default')}';\n";
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

        $this->view->actiondescription = $config->ymoney->actiondescription;
        $error = true;

        // Проверяем, давал ли пользователь права доступа приложения для Яндекс.Денег
        $zayac = $this->_verifyUser($this->action_code_flag . $this->action_code_stat . '-' . $this->_getParam('action_id'));
        $account = $zayac->getAccountInformation();

        // Интерфейс пользователя
        $this->view->user_id = $user_id;
        // ---
        $action_id = intval($this->removeCRLF($this->_getParam('action_id')));
        if (strlen($user_id) > 3) {
            if ($action_id > 0) {
                $actionsModel = new Application_Model_DbTable_Action();
                $action = $actionsModel->getAction($action_id);
                if ($action['id']) {
                    $start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
                    $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);

                    //Информация об акции
                    if ($user_id == $action['user_id'] || $this->view->admin) {
                        $error = false;
                        if ($action['url'] != 'http://' && strlen($action['url'])) {
                            if (!strstr($action['url'], "http://")) {
                                $action['url'] = 'http://' . $action['url'];
                            }
                        } else {
                            $action['url'] = '';
                        }
                        if ($action['completed'] == 'Y') {
                            $action['left'] = 'акция завершена';
                        }
                        
                        $this->_action2Process($action);
                        
//                        if (!$action['date_end'] || $action['date_end'] == '0000-00-00 00:00:00') {
//                            if ($action['date_start'] != '0000-00-00 00:00:00') {
//                                $action['dates'] = 'С ' . $this->_helper->dateFormat($action['date_start']);
//                                if (abs(mktime() - $start_stamp) >= 31536000) {
//                                    $action['dates'] .= ' ' . date('Y', $start_stamp);
//                                }
//                            } else {
//                                $action['dates'] = 'Всегда';
//                            }
//                        } elseif ($action['date_end'] != '0000-00-00 00:00:00') {
//                            if (date('n', $start_stamp) == date('n', $end_stamp)
//                                    &&
//                                    abs($end_stamp - $start_stamp) < 31536000 &&
//                                    (abs(mktime() - $start_stamp) < 31536000) &&
//                                    (abs(mktime() - $end_stamp) < 31536000)) {
//                                $action['dates'] = date('j', $start_stamp) . '  &mdash;  ' . $this->_helper->dateFormat($action['date_end']);
//                            } elseif (
//                                    abs($end_stamp - $start_stamp) < 31536000 &&
//                                    (abs(mktime() - $start_stamp) < 31536000) &&
//                                    (abs(mktime() - $end_stamp) < 31536000)) {
//                                $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
//                            } else {
//                                $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' ' . date('Y', $start_stamp) . ' &mdash; ' .
//                                        $this->_helper->dateFormat($action['date_end']) . ' ' . date('Y', $end_stamp);
//                            }
//
//                            if ($action['completed'] == 'Y') {
//                                $action['left'] = 'акция завершена';
//                            } else {
//                                $dateend = explode(" ", $action['date_end']);
//                                $dateend = $dateend[0];
//                                $dateend = explode("-", $dateend);
//                                $dtime = mktime(0, 0, 0, $dateend[1], $dateend[2], $dateend[0]);
//                                $ctime = mktime();
//                                $fivedays = 86400 * 5;
//                                if (($dtime - $ctime) <= $fivedays) {
//                                    $dn = ceil(($dtime - $ctime) / 86400);
//                                    if ($dn < 5 && $dn > 1) {
//                                        $dt = " дня";
//                                        $dl = " осталось ";
//                                    } elseif ($dn == 1) {
//                                        $dt = " день";
//                                        $dl = " остался ";
//                                    } elseif ($dn >= 5) {
//                                        $dt = " дней";
//                                        $dl = " осталось ";
//                                    } else {
//                                        $action['left'] = 'акция завершена';
//                                    }
//                                    if (($dtime - $ctime) > 0) {
//                                        $action['left'] = $dl . ceil(($dtime - $ctime) / 86400) . $dt;
//                                    }
//                                }
//                            }
//                        }
                        if ($action['required_sum'] > 0 && $action['required_sum'] > ($action['current_sum'] + $action['all_sum'])) {
                            $action['percents'] = (($action['current_sum'] + $action['all_sum']) / $action['required_sum']) * 100;
                            if ($action['percents'] < 1 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum']))
                                $action['percents'] = 1;
                        } elseif ($action['required_sum'] > 0 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum'])) {
                            $action['percents'] = 100;
                        } else {
                            $action['percents'] = false;
                        }

                        if($action['group']==1) {
                        	$usersactionModel = new Application_Model_DbTable_Usersaction();
                        	$friendlist = $usersactionModel->selectfriends($action['id']);
                        	$action['friends'] = $friendlist;
                        	$action['required_sum_group'] = $action['required_sum']/(count($action['friends'])+1)*1.005;
                        	$this->view->redirect_friends_url = 'https://money.yandex.ru/direct-payment.xml?receiver='.
                        			$action['receiver'].'&Formcomment='.urlencode("На акцию в Вконтакте «".$action['name']."»").'&destination='.
                        			urlencode('Для акции «'.$action['name'].'» от пользователя vk.com/id' . $user_id . '. Чтобы отправить деньги анонимно, удалите весь комментарий.') .
                                                '&sum=' . ($action['required_sum_group']) . '&_openstat=socapp;vk;p2p;list&label=vk_' . $action['id'];
                        } else {
                        	$action['required_sum_group'] = 0;
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

                        $action['short'] = addslashes($action['short']);
                        if ($action['required_sum'] > 0) {
                            $action['required_sumF'] = $this->_helper->numberFormat($action['required_sum']);
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
                        $tmp1 = "";
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
                        $this->view->share = $config->ymoney->APP_PAGE . "#app_data=" . $action['id'] . "&t=" . $action['name'];
                        $this->view->redirect_url = $config->ymoney->APP_PAGE . '#app_data=' . $action['id'];
                        $this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
                        $this->view->headMeta()->setProperty('vk:title', $action['name']);
                        $this->view->headMeta()->setProperty('vk:description', $action['description']);
                        // ---
                        // Статистика аккаунта Яндекс.Денег

                        $this->view->balance = 0;
                        $operations = array();
                        $alloperations = array();

                        $operations = $zayac->listOperationHistory('deposition', null, $this->records_on_statpage);
                        $alloperations = $operations['operations'];
                        $this->view->operations = $operations['operations'];

                        while (isset($operations['next_record']) && $operations['next_record'] > 0) {
                            $operations = $zayac->listOperationHistory('deposition', $operations['next_record'], $this->records_on_statpage);
                            $alloperations = array_merge($alloperations, $operations['operations']);
                        }

                        if ($this->_getParam('stat_to') || $this->_getParam('stat_from')) {
                            // Выбраны даты
                            $stat_to = $this->_getParam('stat_to') . " 23:59:59";
                            $stat_from = $this->_getParam('stat_from');

                            $this->view->endhidden = $stat_to;
                            $this->view->starthidden = $stat_from;
                            $this->view->endhiddenstamp = $this->_helper->dateFormat($stat_to, null, true);
                            $this->view->starthiddenstamp = $this->_helper->dateFormat($stat_from, null, true);

                            if ($action['source'] == 'ym') {
                                $operations = array();
                                foreach ($alloperations as $operation) {
                                    $datetime = $operation->getDateTime();
                                    if ($this->_helper->dateFormat($stat_from, null, true) <= $datetime
                                            && $this->_helper->dateFormat($stat_to, null, true) >= $datetime) {
                                        $operations[] = $operation;
                                        $this->view->balance += $operation->getAmount();
                                    }
                                }
                                $this->view->operations_number = count($operations);
                                $this->view->operations = array_slice($operations, 0, $this->records_on_statpage);
/*                            } elseif ($action['source'] == 'fromdate') {
                                $operations = array();
                                foreach ($alloperations as $operation) {
                                    $datetime = $operation->getDateTime();

                                    if ($this->_helper->dateFormat($action['ymfromdate'], null, true) <= $datetime
                                            && $this->_helper->dateFormat($stat_from, null, true) <= $datetime
                                            && $this->_helper->dateFormat($stat_to, null, true) >= $datetime) {
                                        $operations[] = $operation;
                                        $this->view->balance += $operation->getAmount();
                                    }
                                }

                                $this->view->operations_number = count($operations);
                                $this->view->operations = array_slice($operations, 0, $this->records_on_statpage);*/
                            } elseif ($action['source'] == 'fromapp' || $action['source'] == 'fromdate') {
                                $operations = array();
                                foreach ($alloperations as $operation) {
                                    $op = ZenYandexClient::getOperationDetails($operation->getOperationId());
                                    if (isset($op['message'])) {
                                        $message = $op['message'];
                                    } else {
                                        $message = '';
                                    }
                                    if (isset($op['details'])) {
                                        $details = $op['details'];
                                    } else {
                                        $details = '';
                                    }

                                    $text = '«' . $action['name'] . '»';
                                    if ((mb_strstr($message, $text) || mb_strstr($details, $text)) && $this->_helper->dateFormat($stat_from, null, true) <= $datetime
                                            && $this->_helper->dateFormat($stat_to, null, true) >= $datetime) {
                                        $operations[] = $operation;
                                        $this->view->balance += $operation->getAmount();
                                    }
                                }
                                $this->view->operations_number = count($operations);
                                $this->view->operations = array_slice($operations, 0, $this->records_on_statpage);
                            }
                        } else {
                            if ($action['source'] == 'ym') {
                                if ($action['date_end'] != '0000-00-00 00:00:00' && $action['date_end'] != 'NULL' && $action['date_end'] != 0) {
                                    if ($end_stamp < mktime()) {
                                        $this->view->endhidden = $this->_helper->dateFormat($action['date_end'], null, null, true);
                                        $this->view->endhiddenstamp = $this->_helper->dateFormat($action['date_end'], null, true);
                                    } else {
                                        $this->view->endhidden = date('Y-m-d');
                                        $this->view->endhiddenstamp = $this->_helper->dateFormat($this->view->endhidden, null, true);
                                    }
                                } else {
                                    $this->view->endhidden = date('Y-m-d');
                                    $this->view->endhiddenstamp = $this->_helper->dateFormat($this->view->endhidden, null, true);
                                }

                                if ($action['date_start'] != '0000-00-00 00:00:00' && $action['date_start'] != 'NULL'
                                        && $action['date_start'] != 0 && $this->_helper->dateFormat($action['date_start'], null, true) < time()) {
                                    $this->view->starthidden = $this->_helper->dateFormat($action['date_start'], null, null, true);
                                    $this->view->starthiddenstamp = $this->_helper->dateFormat($action['date_start'], null, true);
                                    
                                } else {
                                    $this->view->starthidden = date('Y-m-d');
                                    $this->view->starthiddenstamp = $this->_helper->dateFormat($this->view->starthidden, null, true);
                                }

                                $operations = array();
                                foreach ($alloperations as $operation) {
                                    $datetime = $operation->getDateTime();
                                    if ($start_stamp <= $datetime) {
                                        $operations[] = $operation;
                                        $this->view->balance += $operation->getAmount();
                                    }
                                }
                                $this->view->operations_number = count($operations);
                                $this->view->operations = array_slice($operations, 0, $this->records_on_statpage);
/*                            } elseif ($action['source'] == 'fromdate') {
                                if ($action['date_end'] != '0000-00-00 00:00:00' && $action['date_end'] != 'NULL' && $action['date_end'] != 0) {
                                    if ($end_stamp < mktime()) {
                                        $this->view->endhidden = $this->_helper->dateFormat($action['date_end'], null, null, true);
                                        $this->view->endhiddenstamp = $this->_helper->dateFormat($action['date_end'], null, true);
                                    } else {
                                        $this->view->endhidden = date('Y-m-d');
                                        $this->view->endhiddenstamp = $this->_helper->dateFormat($this->view->endhidden, null, true);
                                    }
                                } else {
                                    $this->view->endhidden = date('Y-m-d');
                                    $this->view->endhiddenstamp = $this->_helper->dateFormat($this->view->endhidden, null, true);
                                }

                                if ($action['ymfromdate'] != '0000-00-00 00:00:00' && $action['ymfromdate'] != 'NULL' && $action['ymfromdate'] != 0) {
                                    $this->view->starthidden = $this->_helper->dateFormat($action['ymfromdate'], null, null, true);
                                    $this->view->starthiddenstamp = $this->_helper->dateFormat($action['date_start'], null, true);
                                } else {
                                    $this->view->starthidden = date('Y-m-d');
                                    $this->view->starthiddenstamp = $this->_helper->dateFormat($this->view->starthidden, null, true);
                                }
                                $operations = array();
                                foreach ($alloperations as $operation) {
                                    $datetime = $operation->getDateTime();
                                    if ($this->_helper->dateFormat($action['ymfromdate'], null, true) <= $datetime) {
                                        $operations[] = $operation;
                                        $this->view->balance += $operation->getAmount();
                                    }
                                }
                                $this->view->operations_number = count($operations);
                                $this->view->operations = array_slice($operations, 0, $this->records_on_statpage);*/
                            } elseif ($action['source'] == 'fromapp' || $action['source'] == 'fromdate') {
                                if ($action['date_end'] != '0000-00-00 00:00:00' && $action['date_end'] != 'NULL' && $action['date_end'] != 0) {
                                    if ($end_stamp < mktime()) {
                                        $this->view->endhidden = $this->_helper->dateFormat($action['date_end'], null, null, true);
                                        $this->view->endhiddenstamp = $this->_helper->dateFormat($action['date_end'], null, true);
                                    } else {
                                        $this->view->endhidden = date('Y-m-d');
                                        $this->view->endhiddenstamp = $this->_helper->dateFormat($this->view->endhidden, null, true);
                                    }
                                } else {
                                    $this->view->endhidden = date('Y-m-d');
                                    $this->view->endhiddenstamp = $this->_helper->dateFormat($this->view->endhidden, null, true);
                                }

                                if ($action['date_start'] != '0000-00-00 00:00:00' && $action['date_start'] != 'NULL'
                                        && $action['date_start'] != 0 && $this->_helper->dateFormat($action['date_start'], null, true) < time()) {
                                    $this->view->starthidden = $this->_helper->dateFormat($action['date_start'], null, null, true);
                                    $this->view->starthiddenstamp = $this->_helper->dateFormat($action['date_start'], null, true);
                                } else {
                                    $this->view->starthidden = date('Y-m-d');
                                    $this->view->starthiddenstamp = $this->_helper->dateFormat($this->view->starthidden, null, true);
                                }
                                $operations = array();
                                foreach ($alloperations as $operation) {
                                    $op = ZenYandexClient::getOperationDetails($operation->getOperationId());
                                    if (isset($op['message'])) {
                                        $message = $op['message'];
                                    } else {
                                        $message = '';
                                    }
                                    if (isset($op['details'])) {
                                        $details = $op['details'];
                                    } else {
                                        $details = '';
                                    }

                                    $text = '"' . $action['name'] . '"';
                                    if (mb_strstr($message, $text) || mb_strstr($details, $text)) {
                                        $operations[] = $operation;
                                        $this->view->balance += $operation->getAmount();
                                    }
                                }
                                $this->view->operations_number = count($operations);
                                $this->view->operations = array_slice($operations, 0, $this->records_on_statpage);
                            }
                        }

                        // Pagination
                        //echo $this->view->operations_number;
                        if ($this->view->operations_number > $this->records_on_statpage) {
                            $this->view->pages = true;
                        } else {
                            $this->view->pages = false;
                        }
                        // ---
                    }
                }
            }
        }
        if ($error) {
            $this->_redirect($this->_helper->url('index', 'index', 'default'));
        }
    }

    private function uni_strsplit($string, $split_length = 1) {
        preg_match_all('`.`u', $string, $arr);
        $arr = array_chunk($arr[0], $split_length);
        $arr = array_map('implode', $arr);
        return $arr;
    }

    public function statpageAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $jsonData = false;
        $user_id = false;
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $this->view->admin = $this->_adminInteface($user_id);
        // Проверяем, давал ли пользователь права доступа приложения для Яндекс.Денег
        $zayac = $this->_verifyUser($this->action_code_flag . $this->action_code_index);
        $action_id = intval($this->removeCRLF($this->_getParam('action_id')));
        $page = intval($this->removeCRLF($this->_getParam('page')));
        if (strlen($user_id) > 3) {
            if ($action_id > 0 && $page > 1) {
                $actionsModel = new Application_Model_DbTable_Action();
                $action = $actionsModel->find($action_id)->current();
                if ($action) {
                    if ($user_id == $action->user_id || $this->_adminInteface($user_id)) {
                        $operations = array();
                        $alloperations = array();

                        $operations = $zayac->listOperationHistory('deposition', null, $this->records_on_statpage);
                        $alloperations = $operations['operations'];

                        while (isset($operations['next_record']) && $operations['next_record'] > 0) {
                            $operations = $zayac->listOperationHistory('deposition', $operations['next_record'], $this->records_on_statpage);
                            $alloperations = array_merge($alloperations, $operations['operations']);
                        }

                        $alloperations1 = array();
                        foreach ($alloperations as $operation) {
                            $datetime = $operation->getDateTime();
                            if ($this->_helper->dateFormat($this->_getParam('date1'), null, true) <= $datetime
                                    && $this->_helper->dateFormat($this->_getParam('date2') . " 23:59:59", null, true) >= $datetime) {
                                $alloperations1[] = $operation;
                            }
                        }
                        $alloperations = $alloperations1;

                        if ($action['source'] == 'ym') {
                            $number_pages = ceil(count($alloperations) / $this->records_on_statpage);
 /*                       } elseif ($action['source'] == 'fromdate') {
                            $this->view->start = $this->_helper->dateFormat($action['ymfromdate']);
                            foreach ($alloperations as $operation) {
                                $datetime = $operation->getDateTime();
                                if (isset($datetime) && $this->_helper->dateFormat($action['ymfromdate'], null, true) <= $datetime) {
                                    $operations[] = $operation;
                                }
                            }
                            $number_pages = ceil(count($operations) / $this->records_on_statpage);
                            //echo "records_on_statpage = ".$this->records_on_statpage."; ";
                            //echo "count(operations) = ".count($operations)."; ";
                            //echo "number_pages = ".$number_pages."; ";
                            //echo "this->records_on_statpage*page = ".$this->records_on_statpage."*".$page." = ".($this->records_on_statpage*$page)."; ";
                            $operations = array_slice($operations, ($this->records_on_statpage * ($page - 1)), $this->records_on_statpage);
                            //echo "count(operations) = ".count($operations)."; ";*/
                        } elseif ($action['source'] == 'fromapp' || $action['source'] == 'fromdate') {
                            foreach ($alloperations as $operation) {
                                $op = ZenYandexClient::getOperationDetails($operation->getOperationId());
                                if (isset($op['message'])) {
                                    $message = $op['message'];
                                } else {
                                    $message = '';
                                }
                                if (isset($op['details'])) {
                                    $details = $op['details'];
                                } else {
                                    $details = '';
                                }

                                $text = '"' . $action['name'] . '"';
                                if (mb_strstr($message, $text) || mb_strstr($details, $text)) {
                                    $operations[] = $operation;
                                }
                            }
                            $number_pages = ceil(count($operations) / $this->records_on_statpage);
                            $operations = array_slice($operations, ($this->records_on_statpage * $page), $this->records_on_statpage);
                        }
                        if ($number_pages > $page) {
                            $next = true;
                        } else {
                            $next = false;
                        }
                        //var_dump($operations);
                        $jsonData = array('operations' => array(), 'next' => $next);
                        //$operations = $operations['operations'];
                        if (is_array($operations)) {
                            foreach ($operations as $operation) {
                                //var_dump($operation->datetime);
                                $jsonData['operations'][] = array(
                                    'datetime' => date("d M", $operation->datetime),
                                    'amount' => (ceil($operation->amount) > $operation->amount) ? number_format($operation->amount, 2, ',', ' ') : number_format($operation->amount, 0, ',', ' '),
                                    'comment' => $operation->title
                                );
                            }
                        }
                    }
                }
            }
        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }

    public function adminstatAction() {
        $config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        // Интерфейс админа
        $user_id = false;
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        if (!$user_id)
            exit;
        $this->view->admin = $this->_adminInteface($user_id);
        if (!$this->view->admin)
            exit;
        $this->view->appurl = $config->ymoney->APP_PAGE;
        $this->view->cancreate = $this->cancreate($user_id);
        
        $actionModel = new Application_Model_DbTable_Action();
        $actions = $actionModel->getAllActions();
        $this->view->countAll = count($actions);
        $actions = $actionModel->getActions(null, true);
        $this->view->countTop = count($actions);
        $actions = $actionModel->getActions(null, null, true);
        $this->view->countDraft = count($actions);
        $actions = $actionModel->getActions(null, null, null, true);
        $this->view->countNew = count($actions);
        $actions = $actionModel->getActions(null, null, null, null, null, true);
        $this->view->countHidden = count($actions);
        $actions = $actionModel->getActions(null, null, null, null, null, null, true);
        $this->view->countBlocked = count($actions);

        $this->view->countThisDay = count($actionModel->getThisDayActions());
        $this->view->countLastDay = count($actionModel->getLastDayActions());

        $this->view->countThisWeek = count($actionModel->getThisWeekActions());
        $this->view->countLastWeek = count($actionModel->getLastWeekActions());
        $this->view->count2Week = count($actionModel->get2WeekActions());
        $this->view->count3Week = count($actionModel->get3WeekActions());

        $this->view->countThisMonth = count($actionModel->getThisMonthActions());
        $this->view->countLastMonth = count($actionModel->getLastMonthActions());
        $this->view->count2Month = count($actionModel->get2MonthActions());

        $this->view->paymentsThisDay = $actionModel->getThisDayPayments();
        $this->view->paymentsLastDay = $actionModel->getLastDayPayments();
        $this->view->paymentsThisWeek = $actionModel->getThisWeekPayments();
        $this->view->paymentsLastWeek = $actionModel->getLastWeekPayments();
        $this->view->payments2Week = $actionModel->get2WeekPayments();
        $this->view->payments3Week = $actionModel->get3WeekPayments();
        $this->view->paymentsThisMonth = $actionModel->getThisMonthPayments();
        $this->view->paymentsLastMonth = $actionModel->getLastMonthPayments();
        $this->view->payments2Month = $actionModel->get2MonthPayments();
    }

    public function informerAction() {
        $config = Zend_Registry::get('appConfig');
        $imgname = $config->ymoney->host . '/i/informerbg.png';
        // Set the enviroment variable for GD
        putenv('GDFONTPATH=' . realpath('.') . '/i/');
        $action_id = $this->removeCRLF($this->_getParam('id'));
        if ($action_id > 0) {
            $id = intval($action_id);
            $actionsModel = new Application_Model_DbTable_Action();
            $action = $actionsModel->find($id)->current();
            if ($action) {
                $action = $actionsModel->getAction($id);
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender();
                header("Content-type: image/png");
                $im = @imagecreatefrompng($imgname)
                        or die("Cannot Initialize new GD image stream");

                $font = '6215';
                mb_internal_encoding('UTF-8');
                $text_color = imagecolorallocate($im, 59, 89, 152);

                if (mb_strlen($action['name']) > 26) {
                    $namepieces = preg_split('[\s,.]+', $action['name']);

                    $name1 = '';
                    $name2 = '';
					if(is_array($namepieces)){
						foreach ($namepieces as $k => $piece) {
							if (mb_strlen($name1 . $piece . ' ') < 26) {
								$name1 .= $piece . ' ';
							} else {
								break;
							}
						}
						foreach ($namepieces as $l => $piece) {
							if ($l >= $k) {
								if (mb_strlen($name2 . $piece . ' ') < 26) {
									$name2 .= $piece . ' ';
								} else {
									$name2 = mb_substr($name2, 0, (mb_strlen($name2) - 1));
									$name2 .= '…';
									break;
								}
							}
						}
					}
                } else {
                    $name1 = $action['name'];
                    $name2 = null;
                }


                imagefttext($im, 10, 0, 13, 45, $text_color, $font, $name1);

                if ($name2) {
                    imagefttext($im, 10, 0, 13, 60, $text_color, $font, $name2);
                }

                if($action['page_id'] != NULL) {
                    $from = mb_strlen($action['Pagename']) <= 40 ? $action['Pagename'] : mb_substr($action['Pagename'], 0, 39) . '...';
                } else {
                    $from = mb_strlen($action['Username']) <= 18 ? $action['Username'] : mb_substr($action['Firstname'], 0, 1) . '. ' . $action['Lastname'];
                }

                $font = '6216';
                imagefttext($im, 8, 0, 13, 77, $text_color, $font, $from);

                $action['left'] = null;
                $action['dates'] = '';
                
                $this->_action2Process($action);
//                $start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
//                $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
//                if (!$action['date_end'] || $action['date_end'] == '0000-00-00 00:00:00') {
//                    if ($action['date_start'] != '0000-00-00 00:00:00') {
//                        $action['dates'] = 'С ' . $this->_helper->dateFormat($action['date_start']);
//                        if (abs(mktime() - $start_stamp) >= 31536000) {
//                            $action['dates'] .= ' ' . date('Y', $start_stamp);
//                        }
//                    } else {
//                        $action['dates'] = 'Всегда';
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
//                        $action['dates'] = date('j', $start_stamp) . ' - ' . $this->_helper->dateFormat($action['date_end']);
//                        $action['starttext'] = 'С ' . $this->_helper->dateFormat($action['date_start']);
//                    }
//                    // Месяцы не совпадают, годы совпадают (+/- 1)
//                    elseif (
//                            abs($end_stamp - $start_stamp) < 31536000 &&
//                            (abs(mktime() - $start_stamp) < 31536000) &&
//                            (abs(mktime() - $end_stamp) < 31536000)
//                    ) {
//                        $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' - ' . $this->_helper->dateFormat($action['date_end']);
//                        $action['starttext'] = 'С ' . $this->_helper->dateFormat($action['date_start']);
//                    }
//                    // Месяцы и годы не совпадают
//                    else {
//                        $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' ' . date('Y', $start_stamp) . ' - ' .
//                                $this->_helper->dateFormat($action['date_end']) . ' ' . date('Y', $end_stamp);
//                        $action['starttext'] = 'С ' . $this->_helper->dateFormat($action['date_start'] . ' ' . date('Y', $start_stamp));
//                    }
//
//                    // Если акция завершена - меняем статус
//
//                    if ($end_stamp < mktime()) {
//                        $action['left'] = 'акция завершена';
//                    } else {
//                        if ($action['completed'] == 'Y') {
//                            $action['left'] = 'акция завершена';
//                        } else {
//                            $dateend = explode(" ", $action['date_end']);
//                            $dateend = $dateend[0];
//                            $dateend = explode("-", $dateend);
//                            $dtime = mktime(0, 0, 0, $dateend[1], $dateend[2], $dateend[0]);
//                            $ctime = mktime();
//                            $fivedays = 86400 * 5;
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
//                                    $action['left'] = 'акция завершена';
//                                }
//                                if (($dtime - $ctime) > 0) {
//                                    $action['left'] = $dl . ceil(($dtime - $ctime) / 86400) . $dt;
//                                }
//                            }
//                        }
//                    }
//                }

                $text_color = imagecolorallocate($im, 76, 76, 76);
                $font = '6216';
                imagefttext($im, 7, 0, 13, 22, $text_color, $font, $action['dates']);
                $text_color = imagecolorallocate($im, 255, 71, 0);
                if ($action['left']) {
                    imagefttext($im, 7, 0, 140, 22, $text_color, $font, $action['left']);
                }

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
                $summ1 = 'Собрано';
                $summ2 = $action['current_sumF'];
                $summ3 = 'из';
                $summ4 = $action['required_sumF'];
                $summ5 = 'руб.';

                if ($action['required_sum'] == 0) {
                    $summ4 = null;
                    $len = mb_strlen($summ1 . ' ' . $summ2 . ' ' . $summ5);
                } else {
                    $len = mb_strlen($summ1 . ' ' . $summ2 . ' ' . $summ3 . ' ' . $summ4 . ' ' . $summ5);
                }
                $len = $len * 5.65;
                $first = 238 / 2 - ceil($len / 2);

                $text_color = imagecolorallocate($im, 76, 76, 76);
                $font = '6216';
                $next = imagefttext($im, 8, 0, $first, 118, $text_color, $font, $summ1);
                $next = $next[2] + 4;
                $text_color = imagecolorallocate($im, 51, 51, 51);
                $font = '6215';
                $next = imagefttext($im, 8, 0, $next, 118, $text_color, $font, $summ2);

                $next = $next[2] + 4;

                if ($summ4) {
                    $text_color = imagecolorallocate($im, 76, 76, 76);
                    $font = '6216';
                    $next = imagefttext($im, 8, 0, $next, 118, $text_color, $font, $summ3);
                    $next = $next[2] + 4;

                    $text_color = imagecolorallocate($im, 51, 51, 51);
                    $font = '6215';
                    $next = imagefttext($im, 8, 0, $next, 118, $text_color, $font, $summ4);
                    $next = $next[2] + 4;
                }
                $text_color = imagecolorallocate($im, 51, 51, 51);
                $font = '6215';
                //if()
                //$rubplace = 155;

                $next = imagefttext($im, 8, 0, $next, 118, $text_color, $font, $summ5);

                if ($action['required_sum'] > 0 && $action['required_sum'] > ($action['current_sum'] + $action['all_sum'])) {
                    $action['percents'] = (($action['current_sum'] + $action['all_sum']) / $action['required_sum']) * 100;
                    if ($action['percents'] < 1 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum']))
                        $action['percents'] = 1;
                } elseif ($action['required_sum'] > 0 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum'])) {
                    $action['percents'] = 100;
                } else {
                    $action['percents'] = false;
                }


                if ($action['percents'] || $action['completed'] == 'Y') {
                    if ($action['percents'] <= 33.3 && $action['completed'] == 'N') {
                        $srcim = @imagecreatefrompng($config->ymoney->host . '/i/tl_1.png');
                    } elseif ($action['percents'] <= 67.6 && $action['completed'] == 'N') {
                        $srcim = @imagecreatefrompng($config->ymoney->host . '/i/tl_2.png');
                    } elseif ($action['percents'] < 100 && $action['completed'] == 'N') {
                        $srcim = @imagecreatefrompng($config->ymoney->host . '/i/tl_3.png');
                    } elseif ($action['completed'] == 'Y' || $action['percents'] >= 100) {
                        $srcim = @imagecreatefrompng($config->ymoney->host . '/i/tl_bg.png');
                    } else {
                        $srcim = @imagecreatefrompng($config->ymoney->host . '/i/tl_bg.png');
                    }

                    @imagecopyresized($im, $srcim, 4, 95, 0, 0, 2.3 * $action['percents'], 6, 1, 6);
                }
                //imagealphablending($im, true);
                imagepng($im);
                imagedestroy($im);
                exit;
            }
        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header("Content-type: image/png");
        $im = @imagecreatefrompng($imgname)
                or die("Cannot Initialize new GD image stream");

        $text_color = imagecolorallocate($im, 233, 14, 91);
        imagestring($im, 2, 10, 30, "No action", $text_color);
        imagealphablending($im, true);
        imagepng($im);
        imagedestroy($im);
    }

    public function biginformerAction() {
        $config = Zend_Registry::get('appConfig');
        $imgname = $config->ymoney->host . '/i/biginformer.png';
        // Set the enviroment variable for GD
        putenv('GDFONTPATH=' . realpath('.') . '/i/');

        $action_id = $this->removeCRLF($this->_getParam('id'));
        if ($action_id > 0) {
            $id = intval($action_id);
            $actionsModel = new Application_Model_DbTable_Action();
            $action = $actionsModel->find($id)->current();
            if ($action) {
                $action = $actionsModel->getAction($id);
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender();
                header("Content-type: image/png");
                $im = @imagecreatefrompng($imgname)
                        or die("Cannot Initialize new GD image stream");

                $font = '6215';
                mb_internal_encoding('UTF-8');
                $text_color = imagecolorallocate($im, 28, 42, 71);

                if (mb_strlen($action['name']) > 52) {
                    $namepieces = preg_split('[\s,.]+', $action['name']);

                    $name1 = '';
                    $name2 = '';

                    foreach ($namepieces as $k => $piece) {
                        if (mb_strlen($name1 . $piece . ' ') < 52) {
                            $name1 .= $piece . ' ';
                        } else {
                            break;
                        }
                    }
                    foreach ($namepieces as $l => $piece) {
                        if ($l >= $k) {
                            if (mb_strlen($name2 . $piece . ' ') < 52) {
                                $name2 .= $piece . ' ';
                            } else {
                                $name2 = mb_substr($name2, 0, (mb_strlen($name2) - 1));
                                $name2 .= '…';
                                break;
                            }
                        }
                    }
                } else {
                    $name1 = $action['name'];
                    $name2 = null;
                }


                imagefttext($im, 11, 0, 19, 45, $text_color, $font, $name1);

                if ($name2) {
                    imagefttext($im, 11, 0, 19, 60, $text_color, $font, $name2);
                }
                $text_color = imagecolorallocate($im, 59, 89, 152);
                if($action['page_id'] != NULL) {
                    $from = mb_strlen($action['Pagename']) <= 40 ? $action['Pagename'] : mb_substr($action['Pagename'], 0, 39) . '...';
                } else {
                    $from = mb_strlen($action['Username']) <= 18 ? $action['Username'] : mb_substr($action['Firstname'], 0, 1) . '. ' . $action['Lastname'];
                }

                $font = '6216';
                imagefttext($im, 9, 0, 19, 77, $text_color, $font, $from);

                $action['left'] = null;
                $action['dates'] = '';
                
                $this->_action2Process($action);
//                $start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
//                $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);
//                if (!$action['date_end'] || $action['date_end'] == '0000-00-00 00:00:00') {
//                    if ($action['date_start'] != '0000-00-00 00:00:00') {
//                        $action['dates'] = 'С ' . $this->_helper->dateFormat($action['date_start']);
//                        if (abs(mktime() - $start_stamp) >= 31536000) {
//                            $action['dates'] .= ' ' . date('Y', $start_stamp);
//                        }
//                    } else {
//                        $action['dates'] = 'Всегда';
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
//                        $action['dates'] = date('j', $start_stamp) . ' – ' . $this->_helper->dateFormat($action['date_end']);
//                        $action['starttext'] = 'С ' . $this->_helper->dateFormat($action['date_start']);
//                    }
//                    // Месяцы не совпадают, годы совпадают (+/- 1)
//                    elseif (
//                            abs($end_stamp - $start_stamp) < 31536000 &&
//                            (abs(mktime() - $start_stamp) < 31536000) &&
//                            (abs(mktime() - $end_stamp) < 31536000)
//                    ) {
//                        $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' – ' . $this->_helper->dateFormat($action['date_end']);
//                        $action['starttext'] = 'С ' . $this->_helper->dateFormat($action['date_start']);
//                    }
//                    // Месяцы и годы не совпадают
//                    else {
//                        $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' ' . date('Y', $start_stamp) . ' – ' .
//                                $this->_helper->dateFormat($action['date_end']) . ' ' . date('Y', $end_stamp);
//                        $action['starttext'] = 'С ' . $this->_helper->dateFormat($action['date_start'] . ' ' . date('Y', $start_stamp));
//                    }
//
//                    // Если акция завершена - меняем статус
//
//                    if ($end_stamp < mktime()) {
//                        $action['left'] = 'акция завершена';
//                    } else {
//                        if ($action['completed'] == 'Y') {
//                            $action['left'] = 'акция завершена';
//                        } else {
//                            $dateend = explode(" ", $action['date_end']);
//                            $dateend = $dateend[0];
//                            $dateend = explode("-", $dateend);
//                            $dtime = mktime(0, 0, 0, $dateend[1], $dateend[2], $dateend[0]);
//                            $ctime = mktime();
//                            $fivedays = 86400 * 5;
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
//                                    $action['left'] = 'акция завершена';
//                                }
//                                if (($dtime - $ctime) > 0) {
//                                    $action['left'] = $dl . ceil(($dtime - $ctime) / 86400) . $dt;
//                                }
//                            }
//                        }
//                    }
//                }

                $text_color = imagecolorallocate($im, 76, 76, 76);
                $font = '6216';
                imagefttext($im, 8, 0, 19, 22, $text_color, $font, $action['dates']);
                $text_color = imagecolorallocate($im, 255, 71, 0);
                if ($action['left']) {
                    imagefttext($im, 8, 0, 380, 22, $text_color, $font, $action['left']);
                }

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
                $summ1 = 'Собрано';
                $summ2 = $action['current_sumF'];
                $summ3 = 'из';
                $summ4 = $action['required_sumF'];
                $summ5 = 'руб.';

                if ($action['required_sum'] == 0) {
                    $summ4 = null;
                    $len = mb_strlen($summ1 . ' ' . $summ2 . ' ' . $summ5);
                } else {
                    $len = mb_strlen($summ1 . ' ' . $summ2 . ' ' . $summ3 . ' ' . $summ4 . ' ' . $summ5);
                }
                $len = $len * 7;
                $first = 500 / 2 - ceil($len / 2);

                $text_color = imagecolorallocate($im, 76, 76, 76);
                $font = '6216';
                $next = imagefttext($im, 10, 0, $first, 133, $text_color, $font, $summ1);
                $next = $next[2] + 4;
                $text_color = imagecolorallocate($im, 51, 51, 51);
                $font = '6215';
                $next = imagefttext($im, 10, 0, $next, 133, $text_color, $font, $summ2);

                $next = $next[2] + 4;

                if ($summ4) {
                    $text_color = imagecolorallocate($im, 76, 76, 76);
                    $font = '6216';
                    $next = imagefttext($im, 10, 0, $next, 133, $text_color, $font, $summ3);
                    $next = $next[2] + 4;

                    $text_color = imagecolorallocate($im, 51, 51, 51);
                    $font = '6215';
                    $next = imagefttext($im, 10, 0, $next, 133, $text_color, $font, $summ4);
                    $next = $next[2] + 4;
                }
                $text_color = imagecolorallocate($im, 51, 51, 51);
                $font = '6215';
                //if()
                //$rubplace = 155;

                $next = imagefttext($im, 10, 0, $next, 133, $text_color, $font, $summ5);

                if ($action['required_sum'] > 0 && $action['required_sum'] > ($action['current_sum'] + $action['all_sum'])) {
                    $action['percents'] = (($action['current_sum'] + $action['all_sum']) / $action['required_sum']) * 100;
                    if ($action['percents'] < 1 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum']))
                        $action['percents'] = 1;
                } elseif ($action['required_sum'] > 0 && $action['required_sum'] <= ($action['current_sum'] + $action['all_sum'])) {
                    $action['percents'] = 100;
                } else {
                    $action['percents'] = false;
                }


                if ($action['percents'] || $action['completed'] == 'Y') {
                    if ($action['percents'] <= 33.3 && $action['completed'] == 'N') {
                        $srcim = @imagecreatefrompng($config->ymoney->host . '/i/tl_1big.png');
                    } elseif ($action['percents'] <= 67.6 && $action['completed'] == 'N') {
                        $srcim = @imagecreatefrompng($config->ymoney->host . '/i/tl_2big.png');
                    } elseif ($action['percents'] < 100 && $action['completed'] == 'N') {
                        $srcim = @imagecreatefrompng($config->ymoney->host . '/i/tl_3big.png');
                    } elseif ($action['completed'] == 'Y' || $action['percents'] >= 100) {
                        $srcim = @imagecreatefrompng($config->ymoney->host . '/i/tl_bg2.png');
                    } else {
                        $srcim = @imagecreatefrompng($config->ymoney->host . '/i/tl_bg2.png');
                    }

                    @imagecopyresized($im, $srcim, 4, 97, 0, 0, (int)(4.9 * $action['percents']), 12, 1, 12);
                }
                //imagealphablending($im, true);
                imagepng($im);
                imagedestroy($im);
                exit;
            }
        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header("Content-type: image/png");
        $im = @imagecreatefrompng($imgname)
                or die("Cannot Initialize new GD image stream");

        $text_color = imagecolorallocate($im, 233, 14, 91);
        imagestring($im, 2, 10, 30, "No action", $text_color);
        imagealphablending($im, true);
        imagepng($im);
        imagedestroy($im);
    }

    public function checknameAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $jsonData = false;
        $user_id = false;
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        $name = $this->removeCRLF($this->_getParam('name'));
        $id = $this->removeCRLF($this->_getParam('id'));
        if ($user_id) {
            $actionsModel = new Application_Model_DbTable_Action();
            $actions = $actionsModel->getActions($user_id, null, null, null, null, null, null, null, null, null, true);
            //echo count($actions);
            if ($id) {
                foreach ($actions as $action) {
                    //echo $action['name'];
                    if ($action['name'] == $name && $action['id'] != $id) {
                        $jsonData = true;
                        break;
                    }
                }
            } else {
                foreach ($actions as $action) {
                    //echo $action['name'];
                    if ($action['name'] == $name) {
                        $jsonData = true;
                        break;
                    }
                }
            }
        }
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($jsonData);
    }
    
    public function loadimageAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
            $this->view->uid = $ymoneyNamespace->vk['viewer_id'];
        if($_POST) {
            
            echo '<pre>';
            // получаем информацию об альбоме или создаем его
            $albumId = 0;
                                  
//            // получаем адрес для загрузки фотографий
//            $result = $this->VK->api('photos.getUploadServer', array(
//                    //'title' => 'Yandex Money Album',
//                    //'privacy' => 0,
//                    //'comment_privacy' => 1,
//                ));
//            $url = $result['response']['upload_url'];
//            
//            $file = readfile($_FILES['file1']['tmp_name']);
//            
//            //создаем объект клиента
//            $client = new Zend_Http_Client();
//            //устанавливаем урл, к которому обратимся
//            $client->setUri($url);
//            //устанавливаем метод
//            $client->setMethod(Zend_Http_Client::POST);
//            //устанавливаем переменные
//            //$client->setParameterPost('name', 'John');
//            $client->setParameterPost('file1', $file);
//            //получаем результат
//            $result = $client->request();
//            if($result->isError()){
//                echo 'Вышла ошибочка за номером '. $result->getStatus();
//            } else {
//                print_r($result);
//            }
            
            
            print_r($_POST);
            print_r($_FILES);
            //print($file);
            print "</pre>";
//            //die();
        }
    }
    
    public function duplicatesAction()
    {
    	$config = Zend_Registry::get('appConfig');
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        // Интерфейс админа
        $user_id = false;
        $user_id = $ymoneyNamespace->vk['viewer_id'];
        if (!$user_id)
            exit;
        $this->view->admin = $this->_adminInteface($user_id);
        if (!$this->view->admin)
            exit;
        $this->view->appurl = $config->ymoney->APP_PAGE;
        $this->view->cancreate = $this->cancreate($user_id);
        
    	$actionModel = new Application_Model_DbTable_Action();
    	if($this->_getParam('check') && $this->_getParam('check')>0) {
    		$check = intval($this->_getParam('check'));
    		$action = $actionModel->find($check)->current();
    		if($action) {
    			$action->checked = 1;
    			$action->dupl = 0;
    			$action->save();
    		}
    	}
    	if($this->_getParam('id') && $this->_getParam('id')>0) {
    		$this->view->id = intval($this->_getParam('id'));
    		$actions = $actionModel->getDuplicates($this->view->id);
    	} else {
    		$actions = $actionModel->getDuplicates();
    	}
    	$this->view->actions = $actions;
    }
    
    public function loadimageajaxAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        require_once APPLICATION_PATH . '/php.php';
    }

    public function adminsAction() {
    	$config = Zend_Registry::get('appConfig');
    	$ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
    	// Интерфейс админа
    	$user_id = false;
    	$user_id = $ymoneyNamespace->vk['viewer_id'];
    	if (!$user_id)
    		exit;
    	$this->view->admin = $this->_adminInteface($user_id);
    	if (!$this->view->admin)
    		exit;
    	$this->view->appurl = $config->ymoney->APP_PAGE;
        $this->view->cancreate = $this->cancreate($user_id);
    	
    	$script = "var deleteadminAjaxUrl = '{$this->_helper->url('deleteadmin', 'actions', 'default')}';\n";
    	$this->view->headScript()->prependScript($script, $type = 'text/javascript');
    	$usersModel = new Application_Model_DbTable_Users();
    	// Обработка формы
    	if ($this->_request->isPost()) {
            $id = $this->removeCRLF($this->_getParam('id'));
            $firstname = $this->removeCRLF($this->_getParam('firstname'));
            $url = $this->removeCRLF($this->_getParam('url'));
            $lastname = $this->removeCRLF($this->_getParam('lastname'));
            $user = null;
            if($id || $url || ($firstname && $lastname)) {
                if($id) $user = $usersModel->getUser($id);
                elseif($url) $user = $usersModel->getUser($url);    			
                else $user = $usersModel->getUser($firstname, $lastname);
                if($user) {
                    $user = $user->current();
                }
                if($user) {
                            $user->admin = 'Y';
                            $user->save();
                } else {
                        $this->view->error = "Такого пользователя нет в базе данных";
                }
            }
    	}
    	
    	
    	
    	$admins = $usersModel->getAdmins();
    	$this->view->admins = $admins;
    }    
    
    public function deleteadminAction() {
    	$ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
    	$config = Zend_Registry::get('appConfig');
    	$jsonData = false;
    	$user_id = $ymoneyNamespace->vk['viewer_id'];
    	$id = $this->removeCRLF($this->_getParam('id'));
    	if ($this->_adminInteface($user_id)) {
    		if (strlen($id) > 0) {
    			$usersModel = new Application_Model_DbTable_Users();
    			$user = $usersModel->find($id)->current();
    			if ($user) {
    				$user->admin = 'N';
    				if ($user->save()) {
    					$jsonData = true;
    				}
    			}
    		}
    	}
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	header('Content-type: application/json; charset=utf-8');
    	echo Zend_Json::encode($jsonData);
    }
    
    public function saveuserajaxAction() {
    	$user_id = $this->removeCRLF($this->_getParam('user_id'));
        $action_id = $this->removeCRLF($this->_getParam('action_id'));
        $shelpusers = new Application_Model_DbTable_Shelpusers();
        $shelpusers->insertuser($user_id, $action_id);
        
        $this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    }
    
    public function fundraisingstatsajaxAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
        $config = Zend_Registry::get('appConfig');
        $jsonData = false;
        $action_id = intval($this->removeCRLF($this->_getParam('action_id')));
        if ($action_id > 0) {
            $actionsModel = new Application_Model_DbTable_Action();
            $action = $actionsModel->find($action_id)->current();
            if ($action) {
                $zayac = new ZenYandexClient($action['token']);
                if($action['source'] == 'ym') {
                    $result = $zayac->getAccountInformation();
                } else {
                    $result = $zayac->fundraisingStats($action_id);
                }
            }
        } else $result = false;

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
        echo Zend_Json::encode($result);
    }
    
    public function savepagesajaxAction() {
        $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
    	$config = Zend_Registry::get('appConfig');
    	$user_id = $ymoneyNamespace->vk['viewer_id'];
        
        $data = $this->_getParam('response');
        foreach($data as $k => $v) {
            if($k > 0) {
                $pagesModel = new Application_Model_DbTable_Pages();
                $sql = "INSERT INTO ym_pages (id, page_id, name, url, user_id) VALUES (null,'" . $v['gid'] . "', '" . $v['name'] . "', 'http://vk.com/" . $v['screen_name'] . "', '" . $user_id . "')
                        ON DUPLICATE KEY UPDATE name = '" . $v['name'] . "', url = 'http://vk.com/" . $v['screen_name'] . "', user_id = '" . $user_id . "'";

                $pagesModel->getAdapter()->query($sql);
            }
        }
        $this->view->pages = 1;//$result;
//        var_dump($data);
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header('Content-type: application/json; charset=utf-8');
//        echo true;
    }
    
    private function _action2Process(&$action) {
        
        $start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
        $end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);

        if (!$action['date_end'] || $action['date_end'] == '0000-00-00 00:00:00') {
            if ($action['date_start'] != '0000-00-00 00:00:00') {
                $action['dates'] = 'С ' . $this->_helper->dateFormat($action['date_start']);
                if (date('Y') - date('Y', $start_stamp) >= 1) {
                    $action['dates'] .= ' ' . date('Y', $start_stamp);
                }
            } else {
                $action['dates'] = 'Всегда';
            }
        } elseif ($action['date_end'] != '0000-00-00 00:00:00') {
            // Месяц и год совпадают (+/- 1)
            if (date('n', $start_stamp) == date('n', $end_stamp)
                    && (date('Y', $end_stamp) - date('Y', $start_stamp) < 1)
                    && (date('Y') - date('Y', $start_stamp) < 1)
                    && (date('Y') - date('Y', $end_stamp) < 1)
            ) {
                $action['dates'] = date('j', $start_stamp) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
            }
            // Месяцы не совпадают, годы совпадают (+/- 1)
            elseif (
                    //преобразовать
                    (date('Y', $end_stamp) - date('Y', $start_stamp) < 1)
                    && (date('Y') - date('Y', $start_stamp) < 1)
                    && (date('Y') - date('Y', $end_stamp) < 1)
            ) {
                $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
            } else {
                $action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' ' . date('Y', $start_stamp) . ' &mdash; ' .
                        $this->_helper->dateFormat($action['date_end']) . ' ' . date('Y', $end_stamp);
            }

            $datestart = explode(' ', $action['date_start']);
            $action['date_start'] = $datestart[0];
            $dateend = explode(' ', $action['date_end']);
            $action['date_end'] = $dateend[0];

            if ($action['completed'] == 'Y') {
                $action['left'] = 'акция завершена';
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
                        $dt = " дня";
                        $dl = " осталось ";
                    } elseif ($dn == 1) {
                        $dt = " день";
                        $dl = " остался ";
                    } elseif ($dn >= 5) {
                        $dt = " дней";
                        $dl = " осталось ";
                    } else {
                        $action['left'] = 'акция завершена';
                    }
                    if (($dtime - $ctime) > 0) {
                        $action['left'] = $dl . ceil(($dtime - $ctime) / 86400) . $dt;
                    }
                }
            }
        }
    }

    /**
     * private function for replace bad chars from the search value
     * returns string
     */
	private function _rqfs($search) {
		return str_replace(array("'",'"'), array("", ""), strip_tags($search));
	}
}

