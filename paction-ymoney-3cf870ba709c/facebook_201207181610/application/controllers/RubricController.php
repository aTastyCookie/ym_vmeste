<?php

class RubricController extends Ymoney_Controller_Action {
	public function getAction() {
		$config = Zend_Registry::get ( 'appConfig' );
		$jsonData = false;
		$key = trim ( $this->_getParam ( 'key' ) );
		if (strlen ( $key ) >= 2) {
			$rubricModel = new Application_Model_DbTable_Rubric ();
			$jsonData = $rubricModel->getRubrics ( $key );
		}
		
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ();
		header ( 'Content-type: application/json; charset=utf-8' );
		echo Zend_Json::encode ( $jsonData );
	}
	
	public function deleteAction() {
		$config = Zend_Registry::get ( 'appConfig' );
		// Интерфейс админа
		$facebook = new Facebook ( array ('appId' => $config->ymoney->APP_ID, 'secret' => $config->ymoney->APP_SECRET ) );
		$user_id = false;
		$user_id = $facebook->getUser ();
		if (! $user_id)
			exit ();
		$jsonData = false;
		$rubricModel = new Application_Model_DbTable_Rubric ();
		if ($this->_getParam ( 'id' ) && $this->_getParam ( 'id' ) > 0) {
			// Delete
			$id = ( int ) trim ( $this->_getParam ( 'id' ) );
			$rubric = $rubricModel->find ( $id )->current ();
			$rubric->delete();
			$jsonData = true;
		}
		
		$this->_helper->layout->disableLayout ();
		$this->_helper->viewRenderer->setNoRender ();
		header ( 'Content-type: application/json; charset=utf-8' );
		echo Zend_Json::encode ( $jsonData );
	}
	
	public function adminAction() {
		$config = Zend_Registry::get ( 'appConfig' );
		// Интерфейс админа
		$facebook = new Facebook ( array ('appId' => $config->ymoney->APP_ID, 'secret' => $config->ymoney->APP_SECRET ) );
		$user_id = false;
		$user_id = $facebook->getUser ();
		if (! $user_id)
			exit ();
		$this->view->admin = $this->_adminInteface ( $user_id );
		if (! $this->view->admin)
			exit ();
		$this->view->appurl = $config->ymoney->APP_PAGE;
		
		$script = "var deleterubricAjaxUrl = '{$this->_helper->url('delete', 'rubric', 'default')}';\n";
		$this->view->headScript ()->prependScript ( $script, $type = 'text/javascript' );
		$rubricModel = new Application_Model_DbTable_Rubric ();
		// Обработка формы
		if ($this->_request->isPost ()) {
			$key = trim ( $this->_getParam ( 'key' ) );
			if (mb_strlen ( $key ) < 3)
				$this->view->error = 'Слишком маленькое слово';
			else {
				if ($this->_getParam ( 'id' ) && $this->_getParam ( 'id' ) > 0) {
					// Edit
					$id = ( int ) trim ( $this->_getParam ( 'id' ) );
					$rubric = $rubricModel->find ( $id )->current ();
					if ($rubric) {
						$rubric->name = $key;
						$rubric->save ();
					}
				} else {
					// Insert
					//echo __LINE__;
					//var_dump(array ('name' => $key ));
					$rubricModel->insert ( array ('name' => $key ) );
				}
			}
		}
		
		$this->view->rubrics = $rubricModel->getRubrics (false, 1000);
	}
	
	private function _adminInteface($user_id) {
		if ($user_id) {
			$userModel = new Application_Model_DbTable_Users ();
			$user = $userModel->find ( $user_id )->current ();
			if ($user && $user->admin == 'Y')
				return true;
		}
		return false;
	}
}

