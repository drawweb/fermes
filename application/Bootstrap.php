<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	
    protected function _initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Default',
            'basePath'  => dirname(__FILE__),
        ));
        
        $adminloader = new Zend_Application_Module_Autoloader(array(
        		'namespace' => 'Admin_',
        		'basePath' => APPLICATION_PATH . '/modules/admin/'));
        
        return $autoloader;
    }
    
    protected function _initViewHelpers()
		{
    		$this->bootstrap('layout');
    		$layout = $this->getResource('layout');
    		$view = $layout->getView();
		}
}
