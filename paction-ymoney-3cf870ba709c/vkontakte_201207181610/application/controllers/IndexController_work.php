<?php

class IndexController extends Ymoney_Controller_Action
{
	private $action_code_flag = '21354-';
	private $action_code_index = '52934';
	private $action_code_my = '48523';
	private $action_code_create = '23895';
	private $action_code_edit = '48754';
	private $action_code_account = '98347';
	private $action_code_stat = '23903';
	private $action_code_callback = '38432';

	private $VK;


    public function init()
    {
        /* Initialize action controller here */
      $config = Zend_Registry::get('appConfig');
      $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');

      $this->view->vksession = $ymoneyNamespace->vk;

      require_once 'vkapi.class.php';

      $this->VK = new vkapi($config->ymoney->APP_ID, $config->ymoney->APP_SECRET, $ymoneyNamespace->vk['api_url']);

      $this->view->appdesc = $config->ymoney->description;
      $this->view->appname = $config->ymoney->APP_NAME;
    }

	protected function initFB() {

    $config = Zend_Registry::get('appConfig');
	$ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
    $user_id = $ymoneyNamespace->vk['viewer_id'];
    $resp = $this->VK->api('getUserSettings', array(
      'uid' => $user_id
    ));
    
    $this->view->vk_user_settings = $resp['response'];

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

    public function indexAction()
    {
    	$config = Zend_Registry::get('appConfig');
    	$ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
		$this->initFB();

		$user_id = $ymoneyNamespace->vk['viewer_id'];

		// Интерфейс админа
		$this->view->admin = $this->_adminInteface($user_id);
		$this->view->appurl = $config->ymoney->APP_PAGE;
                $this->view->appid = $config->ymoney->APP_ID;
		$this->view->appurlorigin = $config->ymoney->APP_PAGE_ORIGIN;
		// Интерфейс пользователя
		$this->view->user_id = $user_id;
		$this->view->cancreate = $this->cancreate($user_id);
		$this->view->host = $config->ymoney->host;
		
		// ---
//print_r($this->_getAllParams());
		if (isset($_GET['app_data']) || $this->_getParam('app_data')) {
			$appdata = $this->_getParam('app_data') ? $this->removeCRLF($this->_getParam('app_data')) : $this->removeCRLF($_GET['app_data']);
			
			if(strstr($appdata, $this->action_code_flag)) {
				$actArray = explode('-', $appdata);
	    		$params = '';
	    		$act = $actArray[1];
		    	switch($act) {
		    		case $this->action_code_index: $act = 'index'; break;
		    		case $this->action_code_my: $act = 'my'; break;
		    		case $this->action_code_create: $act = 'create'; break;
		    		case $this->action_code_account: $act = 'account'; break;
		    		case $this->action_code_edit: $act = 'edit'; if(isset($actArray[2])) $params = '?action_id='.$actArray[2]; break;
		    		case $this->action_code_stat: $act = 'stat'; if(isset($actArray[2])) $params = '?action_id='.$actArray[2]; break;
		    		default: $act = 'index';
    			}
    			
    			$this->_redirect($this->_helper->url($act, 'actions', 'default').$params);
			} else {
				$this->viewac($appdata);
			}
    	} else {

	    	// Главная страница приложения
	
			$this->view->headTitle($config->ymoney->APP_NAME);
			$this->view->headMeta($config->ymoney->description2, 'description');
	
			$this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
			$this->view->headMeta()->setProperty('vk:title', $config->ymoney->APP_NAME);
			$this->view->headMeta()->setProperty('vk:type', "website");
			$this->view->headMeta()->setProperty('vk:url', $this->view->appurlorigin);
			$this->view->headMeta()->setProperty('vk:app_id', $config->ymoney->APP_ID);
			$this->view->headMeta()->setProperty('vk:image', 'http://ym.dev.squirrels-dev.com/i/app_75.jpg');
			$this->view->headMeta()->setProperty('vk:description', $config->ymoney->description2);
    		
	        $actionsModel = new Application_Model_DbTable_Action();
	        $actions = $actionsModel->getActions(null, true);
	        //echo count($actions);
	        $actions = array_merge($actions, $actionsModel->getActions(null, null, null, null, null, null, null, true, 5));
	        //echo count($actions);
	        $actions = $this->sortactions($actions);

	        $this->view->hasactions = false;
	        if ($user_id) {
	        	$hasacarr = $actionsModel->getActions($user_id);
		        if(count($hasacarr)>0) {
		        	$this->view->hasactions = true;
		        }
	        }


	        foreach($actions as $k=>$action) {
	        	$start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
	    		$end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);

	        	if(!$action['date_end'] || $action['date_end']=='0000-00-00 00:00:00') {
	        		if($action['date_start']!='0000-00-00 00:00:00') {
	        			$actions[$k]['dates'] = 'С '.$this->_helper->dateFormat($action['date_start']);
	        			if(abs(mktime() - $start_stamp)>=31536000) {
	        				$actions[$k]['dates'] .= ' '.date('Y', $start_stamp);
	        			}
	        		} else {
	        			$actions[$k]['dates'] = 'Всегда';
	        		}

	        	} elseif($action['date_end']!='0000-00-00 00:00:00') {
	        		if(date('n', $start_stamp) == date('n', $end_stamp)
	        		&& abs($end_stamp - $start_stamp)<31536000
	        		&& (abs(mktime() - $start_stamp)<31536000)
	        		&& (abs(mktime() - $end_stamp)<31536000)
	        		) {
	        			$actions[$k]['dates'] = date('j', $start_stamp) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
	        		} elseif(
	        		abs($end_stamp - $start_stamp)<31536000
	        		&& (abs(mktime() - $start_stamp)<31536000)
	        		&& (abs(mktime() - $end_stamp)<31536000)
	        		) {
	        			$actions[$k]['dates'] = $this->_helper->dateFormat($action['date_start']) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
	        		} else {
	        			$actions[$k]['dates'] = $this->_helper->dateFormat($action['date_start']). ' ' . date('Y', $start_stamp) . ' &mdash; ' .
	        			$this->_helper->dateFormat($action['date_end']) . ' ' . date('Y', $end_stamp);
	        		}

	        		// Если акция завершена - меняем статус
	        		if($end_stamp < mktime()) {
	        			if($action['completed'] == 'N') {
		        			$ac = $actionsModel->find($action['id'])->current();
		        			$ac->completed = 'Y';
		        			$ac->save();
		        			unset($ac);
		        			unset($actions[$k]);
	        			}
	        			continue;
	        		}

	        		if($action['completed']=='Y') {
	        			$actions[$k]['left'] = 'акция завершена';
	        		} else {
		        		$dateend = explode(" ", $action['date_end']);
		        		$dateend = $dateend[0];
		        		$dateend = explode("-", $dateend);
		        		$dtime = mktime (0, 0, 0, $dateend[1], $dateend[2], $dateend[0]);
		        		$ctime = mktime();
		        		$fivedays = 86400*5;
		        		if(($dtime-$ctime)<=$fivedays) {
		        			$dn = ceil(($dtime-$ctime)/86400);
		        			if($dn<5 && $dn>1) {
		        				$dt = " дня";
		        				$dl = " осталось ";
		        			} elseif($dn==1) {
		        				$dt = " день";
		        				$dl = " остался ";
		        			} elseif($dn>=5) {
		        				$dt = " дней";
		        				$dl = " осталось ";
		        			} else {
		        				$actions[$k]['left'] = 'акция завершена';
		        			}
		        			if(($dtime-$ctime)>0) {
		        				$actions[$k]['left'] = $dl.ceil(($dtime-$ctime)/86400).$dt;
		        			}
		        		}
	        		}
	        	}

		        if(strlen($action['name'])==0) {
	        		$actions[$k]['name'] = 'Без названия';
	        	}
		        if($action['required_sum']>0 && $action['required_sum']>($action['current_sum']+$action['all_sum'])) {
	        		$actions[$k]['percents'] = (($action['current_sum']+$action['all_sum'])/$action['required_sum'])*100;
	        		if($actions[$k]['percents']<1) $actions[$k]['percents'] = 1;
	        	} elseif ($action['required_sum']>0 && $action['required_sum']<=($action['current_sum']+$action['all_sum'])) {
	        		$actions[$k]['percents'] = 100;
	        	} else {
	        		$actions[$k]['percents'] = false;
	        	}
	        	
	        	if(strlen($action['name'])==0) {
	        		$actions[$k]['name'] = 'Без названия';
	        	}
	        	
	        	if($action['required_sum']>0) {
	        		$actions[$k]['required_sumF'] = $this->_helper->numberFormat($action['required_sum']);
	        	} else {
		        	$actions[$k]['required_sumF'] = 0;
		        }
	        	
	        	if($action['current_sum']>0 || $action['all_sum']>0) {
	        		$actions[$k]['current_sumF'] = $this->_helper->numberFormat(($action['current_sum']+$action['all_sum']));
                                if(($action['current_sum']+$action['all_sum'])<1000000 && $action['required_sum']>=1000000)
                                    $actions[$k]['current_sum_suffix'] = ' руб. ';
                                else
                                    $actions[$k]['current_sum_suffix'] = '';
	        	} else {
		        	$actions[$k]['current_sumF'] = 0;
		        }
	        }
	        $this->view->actions = $actions;

	        $this->view->widthkoef = $config->ymoney->widthkoef;
    	}
    }

    public function cancreate($user_id)
    {
    	$actionsModel = new Application_Model_DbTable_Action();
    	if($user_id) {
	    	$actions = $actionsModel->getActions($user_id, null, null, null, null, null, null, null, null, null, true);
	    	$i=0;
	    	foreach($actions as $action) {
	    		if($this->_helper->dateFormat($action['date_start'], null, true) >= mktime(0, 0, 0, date('m'), date('d'), date('Y'))) {
	    			$i++;
	    		}
	    		if($i==5) {
	    			return false;
	    			break;
	    		}
	    	}
    	}
    	return true;
    }

	public function viewac($id)
    {
      $config = Zend_Registry::get('appConfig');
      $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
      $user_id = $ymoneyNamespace->vk['viewer_id'];
    	$id = intval($id);

    	if($id>0) {
    		$script = "var stopAjaxUrl = '{$this->_helper->url('stop', 'actions', 'default')}';\n";
        $script .= "var startAjaxUrl = '{$this->_helper->url('start', 'actions', 'default')}';\n";
    		$script .= "var getfriendsAjaxUrl = '{$this->_helper->url('getfriends', 'index', 'default')}';\n";

    		if($this->view->admin) {
    			$script .= "var topAjaxUrl = '{$this->_helper->url('top', 'actions', 'default')}';\n";
    			$script .= "var hideAjaxUrl = '{$this->_helper->url('hide', 'actions', 'default')}';\n";
    			$script .= "var blockAjaxUrl = '{$this->_helper->url('block', 'actions', 'default')}';\n";
    			$script .= "var untopAjaxUrl = '{$this->_helper->url('untop', 'actions', 'default')}';\n";
    			$script .= "var unhideAjaxUrl = '{$this->_helper->url('unhide', 'actions', 'default')}';\n";
    			$script .= "var unblockAjaxUrl = '{$this->_helper->url('unblock', 'actions', 'default')}';\n";
    		}
    		$this->view->headScript()->prependScript($script, $type = 'text/javascript');

	    	$actionModel = new Application_Model_DbTable_Action();
	    	$action = $actionModel->getAction($id);

	    	if(isset($action['id'])) {
	    		$this->view->error = null;
	    		if($this->view->user_id && ($action['blocked']=='Y' || $action['draft']=='Y') && $action['user_id']==$this->view->user_id) {
	    			if($action['blocked']=='Y' && $action['draft']=='N') $this->view->error = 'акция заблокирована администратором';
	    			if($action['blocked']=='N' && $action['draft']=='Y') $this->view->error = 'акция сохранена как черновик и не опубликована';
	    			if($action['blocked']=='Y' && $action['draft']=='Y') $this->view->error = 'акция заблокирована администратором, сохранена как черновик и не опубликована';

	    		} elseif(($action['blocked']=='Y' || $action['draft']=='Y') && !$this->view->admin) {
	    			$this->_redirect($this->_helper->url('index', 'actions', 'default').'?no_action=1');
	    		}

	    		$start_stamp = $this->_helper->dateFormat($action['date_start'], null, true);
	    		$end_stamp = $this->_helper->dateFormat($action['date_end'], null, true);

	    		if($action['completed']=='Y') {
		        	$action['left'] = 'акция завершена';
		        }

	    		if(!$action['date_end'] || $action['date_end']=='0000-00-00 00:00:00') {
	    			
	    			if($action['date_start']!='0000-00-00 00:00:00') {
		        		$action['dates'] = 'С '.$this->_helper->dateFormat($action['date_start']);
		    			if(abs(mktime() - $start_stamp)>=31536000) {
	        				$action['dates'] .= ' '.date('Y', $start_stamp);
	        			} 
	    			} else {
	        			$action['dates'] = 'Всегда';
	        		}
        			$action['starttext'] = $action['dates'];
        			
	        	} elseif($action['date_end']!='0000-00-00 00:00:00') {
	        		// Месяц и год совпадают (+/- 1)
	        		if(
	        		date('n', $start_stamp) == date('n', $end_stamp)
	        		&& 
	        		abs($end_stamp - $start_stamp)<31536000 &&
	        		(abs(mktime() - $start_stamp)<31536000) &&
	        		(abs(mktime() - $end_stamp)<31536000)
	        		) {
	        			$action['dates'] = date('j', $start_stamp) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
	        			$action['starttext'] = 'С '.$this->_helper->dateFormat($action['date_start']);
	        		} 
	        		// Месяцы не совпадают, годы совпадают (+/- 1)
	        		elseif(
	        		abs($end_stamp - $start_stamp)<31536000 &&
	        		(abs(mktime() - $start_stamp)<31536000) &&
	        		(abs(mktime() - $end_stamp)<31536000)
	        		) {
	        			$action['dates'] = $this->_helper->dateFormat($action['date_start']) . ' &mdash; ' . $this->_helper->dateFormat($action['date_end']);
	        			$action['starttext'] = 'С '.$this->_helper->dateFormat($action['date_start']);
	        		} 
	        		// Месяцы и годы не совпадают
	        		else {
	        			$action['dates'] = $this->_helper->dateFormat($action['date_start']). ' ' . date('Y', $start_stamp) . ' &mdash; ' .
	        			$this->_helper->dateFormat($action['date_end']) . ' ' . date('Y', $end_stamp);
	        			$action['starttext'] = 'С '.$this->_helper->dateFormat($action['date_start'].' '.date('Y', $start_stamp));
	        		}
	        		// Если акция завершена - меняем статус
	        		if($end_stamp < mktime()) {
	        			if($action['completed'] == 'N') {
		        			$ac = $actionModel->find($id)->current();
		        			$ac->completed = 'Y';
		        			$ac->save();
		        			unset($ac);
	        			}
	        			$action['left'] = 'акция завершена';
	        		} else {
	        			if($action['completed']=='Y') {
		        			$action['left'] = 'акция завершена';
		        		} else {
			        		$dateend = explode(" ", $action['date_end']);
			        		$dateend = $dateend[0];
			        		$dateend = explode("-", $dateend);
			        		$dtime = mktime(0, 0, 0, $dateend[1], $dateend[2], $dateend[0]);
			        		$ctime = time();
			        		$fivedays = 86400*5;
			        		//echo $dtime-$ctime;
			        		if(($dtime-$ctime)<=$fivedays) {
			        			$dn = ceil(($dtime-$ctime)/86400);
			        			if($dn<5 && $dn>1) {
			        				$dt = " дня";
			        				$dl = " осталось ";
			        			} elseif($dn==1) {
			        				$dt = " день";
			        				$dl = " остался ";
			        			} elseif($dn>=5) {
			        				$dt = " дней";
			        				$dl = " осталось ";
			        			} else {
			        				$action['left'] = 'акция завершена';
			        			}
			        			if(($dtime-$ctime)>0) {
			        				$action['left'] = $dl.ceil(($dtime-$ctime)/86400).$dt;
			        			}
			        		}
		        		}
	        		}
	        	}
	        	
	        	//echo $action['dates']; //exit;

	        	if(isset($action['left']) && $action['left'] == 'акция завершена') {
		    		$this->view->error = 'Сбор денег по этой акции уже закончен, но вы можете поддержать другое доброе дело.';
	        	}

	    		if($action['required_sum']>0 && $action['required_sum']>($action['current_sum']+$action['all_sum'])) {
	        		$action['percents'] = (($action['current_sum']+$action['all_sum'])/$action['required_sum'])*100;
	        		if($action['percents']<1) $action['percents'] = 1;
	        	} elseif ($action['required_sum']>0 && $action['required_sum']<=($action['current_sum']+$action['all_sum'])) {
	        		$action['percents'] = 100;
	        	} else {
	        		$action['percents'] = false;
	        	}

	        	$action['description'] = nl2br($action['description']);

	        	$action['short'] = str_replace('<br />', ' ', $action['description']);
	        	$short = $this->uni_strsplit($action['short']);
	        	$action['short'] = '';
	        	foreach($short as $sym) {
	        		if(ord($sym) != 10 && ord($sym)!=13) {
	        			$action['short'] .= $sym;
	        		}
	        	}

	        	//$action['short'] = mb_substr(addslashes($action['short']), 0, 275).'…';
	        	$action['short'] = addslashes($action['short']);
	        	if($action['required_sum']>0) {
	        		$action['required_sumF'] = $this->_helper->numberFormat($action['required_sum'], true);
	        	} else {
	        		$action['required_sumF'] = 0;
	        	}
	    		if($action['current_sum']>0 || $action['all_sum']>0) {
	    			$action['current_sumF'] = $this->_helper->numberFormat(($action['current_sum']+$action['all_sum']), true);
	        	} else {
	        		$action['current_sumF'] = 0;
	        	}
	    		$this->view->action = $action;
	    		$this->view->widthkoef = $config->ymoney->bigwidthkoef;

	    		$photoModel = new Application_Model_DbTable_Photo();
	          $this->view->photos = array();
	          $this->view->photos = $photoModel->getPhotosByAction($action['id']);
				$photos = array();
	          if(count($this->view->photos)>0) {
		          $photosIds = implode(',', $this->view->photos);
		
		          
		          $albums = $this->VK->api('photos.getAlbums', array(
		            //'access_token' => $ymoneyNamespace->vk['access_token'],
		            'uid' => $action['user_id']
		          ));
                          if(!isset($albums['response']))
                              $albums['response'] = array();
	
		          foreach($albums['response'] as $album) {
		
		            $result = $this->VK->api('photos.get', array(
		              //'access_token' => $ymoneyNamespace->vk['access_token'],
		              'uid' => $action['user_id'],
		              'aid' => $album['aid'],
		              'pids' => $photosIds
		            ));
		
		            if(isset($result['response']) && $result['response']) {
		
		              $photos = array_merge($photos, $result['response']);
		            }
		          }
	          }
				$this->view->photos = $photos;
		          
				$this->view->share = $config->ymoney->APP_PAGE . '#app_data='. $action['id'];
				$this->view->redirect_url = $config->ymoney->APP_PAGE . '#app_data=' . $action['id'];
		
				$this->view->headTitle($action['name']);
				$this->view->headMeta($config->ymoney->actiondescription, 'description');
				$this->view->actiondescription = $config->ymoney->actiondescription;
				$this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
				$this->view->headMeta()->setProperty('vk:title', $config->ymoney->actiondescription);
				$this->view->headMeta()->setProperty('vk:type', "website");
				$this->view->headMeta()->setProperty('vk:url', $this->view->redirect_url);
				$this->view->headMeta()->setProperty('vk:app_id', $config->ymoney->APP_ID);
				$this->view->headMeta()->setProperty('vk:image', 'http://ym.dev.squirrels-dev.com/i/app_75.jpg');
				$this->view->headMeta()->setProperty('vk:description', strip_tags($action['description']));
				//$this->view->headMeta()->setProperty('vk:description', $config->ymoney->actiondescription);

				if(strlen($user_id)>3) {
			        $friendsresult = $this->VK->api('friends.get', array(
			          'uid' => $ymoneyNamespace->vk['viewer_id'],
			          'fields' => 'uid,first_name,last_name,nickname,photo'
			        ));
			        $friends = array();
			        if(isset($friendsresult['response']) && is_array($friendsresult['response'])) {
				        foreach($friendsresult['response'] as $value) {			
				          $friends[] = array(
				            'fid' => $value['uid'],
				            'name' => $value['first_name'].(!empty($value['last_name']) ? ' '.$value['last_name'] : ''),
				            'photo' => $value['photo']
				          );
				        }
			        }
			        $this->view->friends = $friends;
				}
				

	    	} else {
	    		$this->_redirect($this->_helper->url('index', 'actions', 'default').'?no_action=1');
	    	}
    	}
    }

    public function getfriendsAction()
    {
      $ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
      $config = Zend_Registry::get('appConfig');
      $jsonData = array();

      $user_id = $ymoneyNamespace->vk['viewer_id'];

      if(strlen($user_id)>3) {

        $result = $this->VK->api('friends.get', array(
          //'access_token' => $ymoneyNamespace->vk['access_token'],
          'uid' => $ymoneyNamespace->vk['viewer_id'],
          'fields' => 'uid,first_name,last_name,nickname,photo'
        ));

        $aids = array();
        foreach($result['response'] as $value) {

          $aids[] = array(
            'fid' => $value['uid'],
            'name' => $value['first_name'].(!empty($value['last_name']) ? ' '.$value['last_name'] : ''),
            'photo' => $value['photo']
          );
        }

        $jsonData = $aids;
      }

      foreach($jsonData as $k=>$photo) {
        $jsonData[$k]['selected'] = 0;
      }

      if($this->_getParam('action_id')) {
        $action = $this->removeCRLF($this->_getParam('action_id'));
        $photoModel = new Application_Model_DbTable_Photo();
        $selectedphotos = array();
        $selectedphotos = $photoModel->getPhotosByAction($action);
        foreach($jsonData as $k=>$photo) {
          if(in_array($photo['pid'], $selectedphotos)) {
            $jsonData[$k]['selected'] = 1;
          }
        }
      }

      $this->_helper->layout->disableLayout();
      $this->_helper->viewRenderer->setNoRender();
      header('Content-type: application/json; charset=utf-8');
      echo Zend_Json::encode($jsonData);
    }
	private function uni_strsplit($string, $split_length=1)
	{
	     preg_match_all('`.`u', $string, $arr);
	     $arr = array_chunk($arr[0], $split_length);
	     $arr = array_map('implode', $arr);
	     return $arr;
	}

    public function redirectAction()
    {
    	$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	    $config = Zend_Registry::get('appConfig');
    	if($this->_getParam('action_id') && intval($this->_getParam('action_id'))>0) {
    		$this->_redirect($config->ymoney->APP_PAGE_PAGE.'#app_data='.intval($this->_getParam('action_id')).'&ref=nf');
    	} else {
    		$this->_redirect($this->_helper->url('index', 'index', 'default'));
    	}
    }


    private function _adminInteface($user_id)
    {
    	if($user_id) {
			$userModel = new Application_Model_DbTable_Users();
   			$user = $userModel->find($user_id)->current();
   			if($user && $user->admin == 'Y') {
   				return true;
   			}
		}
		return false;
    }
}

