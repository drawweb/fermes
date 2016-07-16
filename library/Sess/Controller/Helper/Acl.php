<?php

class Sess_Controller_Helper_Acl extends Zend_Controller_Plugin_Abstract
{
	public $acl;
	
	public function __construct()
	{
		$this->acl = new Zend_Acl();
	}
	public function setRoles()
	{
		$this->acl->addRole(new Zend_Acl_Role('default'));
		$this->acl->addRole(new Zend_Acl_Role('admin'));
	}
	
	public function setResources()
	{
		$this->acl->add(new Zend_Acl_Resource('default'));
		$this->acl->add(new Zend_Acl_Resource('admin'));
	}
	
	public function setPrivilages()
	{
		$this->acl->deny('default','admin');
		
		$this->acl->allow('admin');
		$this->acl->allow('default','default');	
	}
	public function setAcl()
	{
		Zend_Registry::set('acl',$this->acl);
	}
}

