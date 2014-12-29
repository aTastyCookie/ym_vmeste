<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initAutoload()
    {
        $modelLoader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Application',
            'basePath' => APPLICATION_PATH));

        require_once 'Ymoney/Controller/Action.php';
        require_once 'Zen/Yandex/Client.php';
	require_once 'Ymoney/facebook.php';
        
        return $modelLoader;
    }
    
	public function _initConfig()
	{
		$config = new Zend_Config_Ini(
    		APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'application.ini',
    		APPLICATION_ENV
    	);

    	Zend_Registry::set('appConfig', $config);

    	return $config;
	}
	
	

	protected function _initView()
    {
        $view = new Zend_View();
        $view->doctype('XHTML1_STRICT');
        $view->setEncoding('UTF-8');
        $view->headMeta()
                ->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8')
                ->appendHttpEquiv('Content-Language', 'en-US')
                ->appendHttpEquiv('pragma', 'no-cache')
                ->appendHttpEquiv('Cache-Control', 'no-cache');

        $view->addHelperPath('Ymoney/View/Helpers', 'Ymoney_View_Helper');
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);

        return $view;
    }
    
	protected function _initHelper()
    {
		Zend_Controller_Action_HelperBroker::addPrefix('Ymoney_Helper');
    }
    

    
	public function _initSetTranslations()
    {
        $translate = new Zend_Translate('csv', APPLICATION_PATH . '/../languages/ru.csv', 'ru');        
        $translate->addTranslation(
	    	array(
                'content' => APPLICATION_PATH . '/../languages/en.csv',
		        'locale'  => 'en',
		        'clear'   => true
	    	)
		);				
		$ymoneyNamespace = new Zend_Session_Namespace('YmoneyNamespace');
		if (isset($ymoneyNamespace->language)) {
			$translate->setLocale($ymoneyNamespace->language);
		} else {
			$defaultLanguage = 'ru';
			$defaultLanguageArray = array();
			if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$defaultLanguageArray = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			$defaultLanguageArray = explode(',', $defaultLanguageArray[0]);
			}
			foreach ($defaultLanguageArray as $dl) {
				if (in_array($dl,array('en','ru'))) {
					$defaultLanguage = $dl;
					break;
				}
			}
			$ymoneyNamespace->language = $defaultLanguage;
			$translate->setLocale($ymoneyNamespace->language);
		}
        Zend_Registry::set('TranslationObject', $translate);
        return $translate;        
    }
	public function _initDb()
	{
        $this->bootstrap('config');
        $config = $this->getResource('config');
		$db = Zend_Db::factory($config->resources->db);
		Zend_Registry::set('db', $db);
		Zend_Db_Table_Abstract::setDefaultAdapter($db);

        return $db;
	}
    
}

