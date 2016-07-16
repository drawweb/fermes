<?php

class Sess_Controller_Plugins_Session extends Zend_Controller_Plugin_Abstract
{
    private $_session;

    private $_clientHeaders;

    public function __construct()
    {
        $this->_session       = Zend_Registry::get('session');
        $this->_clientHeaders = $_SERVER['HTTP_USER_AGENT'];
        if (array_key_exists('HTTP_ACCEPT', $_SERVER)) {
            $this->_clientHeaders .= $_SERVER['HTTP_ACCEPT'];
        }
        $this->_clientHeaders = md5($this->_clientHeaders);
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if(!Zend_Auth::getInstance()->hasIdentity() && $this->getRequest()->getModuleName() != 'default') {
        	
            Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoUrlAndExit('/index/expired');
        	
            if ($this->_session->clientBrowser != $this->_clientHeaders) {
                Zend_Session::destroy();
                Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoUrlAndExit('/index/expired');
                $this->_response->setHttpResponseCode(403);
                $this->_response->clearBody();
                $this->_response->sendResponse();  
        		exit;
            }
        }
    }

    public function dispatchLoopShutdown()
    {
    	if($this->getRequest()->isXmlHttpRequest()):
    		
    	else:
    		$this->_session->requestUri = $this->getRequest()->getRequestUri();
    	endif;
        $this->_session->clientBrowser = $this->_clientHeaders;
    }
}
