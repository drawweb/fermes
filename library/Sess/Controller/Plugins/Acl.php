<?php

class Sess_Controller_Plugins_Acl extends Zend_Controller_Plugin_Abstract

{
	private $_session;
	
	public function __construct()
	{
		$this->_session = Zend_Registry::get('session');
	}
	
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$acl = Zend_Registry::get('acl');
		$usersNs = $this->_session->ACL_ROLES;
		If($usersNs=='' || !$usersNs){
			$roleName='default';
		} else {
			$roleName=$usersNs;
		}
		$module = $request->getModuleName();
		$controller = $request->getControllerName();
		$action = $request->getActionName();
		$resource = "{$module}-{$controller}";
		if ($acl->has($resource)) {
			if(!$acl->isAllowed($roleName,$resource, $action)){
				$request->setModuleName('default');
				$request->setControllerName('error');
				$request->setActionName('privileges');
			}
		}
	}
}