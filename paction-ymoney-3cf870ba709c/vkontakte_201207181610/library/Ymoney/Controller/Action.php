<?php
class Ymoney_Controller_Action extends Zend_Controller_Action
{
    protected $_translator;

    public function preDispatch()
    {
        if ($this->_request->isXmlHttpRequest()) {
			$this->_helper->layout->disableLayout();
		}

        $this->view->params = $this->getConfigParams();

		$script = "
			var baseUrl = '{$this->view->baseUrl()}';
		";
            
		$this->view->headScript()->prependScript($script, $type = 'text/javascript');

        $actionJsFile = 'js' . '/' . $this->_request->getModuleName() . '/' .
                    $this->_request->getControllerName() . '/' .
                    $this->_request->getActionName() . '.js';
        if(file_exists($actionJsFile)) {
            $this->view->headScript()->appendFile($this->view->baseUrl($actionJsFile . '?=' . time()));
        }

        $this->_translator = Zend_Registry::get('TranslationObject');
        $this->view->translator = $this->_translator;

        $this->view->request = $this->_request;        
    }
    
    public function getConfig()
    {
        return Zend_Registry::get('appConfig');
    }

    public function getConfigParam($var = null)
    {
        return (isset(Zend_Registry::get('appConfig')->params->{$var})) ?
                    Zend_Registry::get('appConfig')->params->{$var} : null;
    }

    public function getConfigParams()
    {
        return Zend_Registry::get('appConfig')->params;
    }    
    
    public function serverUrl($requestUri = null)
    {
        $baseHost = $this->getConfigParam('baseHost');

        if ($baseHost) {
            if ($requestUri === true) {
                $path = $_SERVER['REQUEST_URI'];
            } else if (is_string($requestUri)) {
                $path = $requestUri;
            } else {
                $path = '';
            }

            return $baseHost . $path;
        } else {
            return $this->view->serverUrl($requestUri);
        }
    }
}