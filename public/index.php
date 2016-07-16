<?php

$rootPath = dirname(dirname(__FILE__));
$appPath  = $rootPath . DIRECTORY_SEPARATOR . 'application';
$confPath = $appPath  . DIRECTORY_SEPARATOR . 'configs';

set_include_path(get_include_path() . 
PATH_SEPARATOR . $appPath . 
PATH_SEPARATOR . $rootPath . DIRECTORY_SEPARATOR . 'library' . 
PATH_SEPARATOR . $appPath  . DIRECTORY_SEPARATOR . 'models');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
    
    /** Zend_Application */
require_once 'Zend/Application.php';  

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV, 
    APPLICATION_PATH . '/configs/application.ini'
);

define('CACHE_LIFETIME', 3600);

// ******************** CACHE *******************************

// crÃ©ation du cache pour les composants ZF l'acceptant
Sess_Cache::setup(CACHE_LIFETIME);
// cache automatique des fichiers de configuration
$cacheInstance = Sess_Cache::getCacheInstance();
Sess_Config::setBackendCache($cacheInstance->getBackend());

$configSession = new Zend_Config_Ini(APPLICATION_PATH . '/configs/session.ini', 'production');

// Configuration de la session (impÃ©rativement avant son dÃ©marrage)
Zend_Session::setOptions($configSession->toArray());
Zend_Session::setOptions(array('save_path' => $appPath . $configSession->save_path));
Zend_Session::setoptions(array());

// Partage (et crÃ©ation ou restauration) de l'objet de session dans le registre
// Ce premier appel Ã  new Zend_Session_Namespace dÃ©marre la session PHP
Zend_Registry::set('session', $session = new Zend_Session_Namespace($configSession->name));
Zend_Controller_Action_HelperBroker::addPrefix('Sess_Controller_ActionHelpers');
Zend_View_Helper_PaginationControl::setDefaultViewPartial('common/pagination_control.phtml');


/*$helper = new Sess_Controller_Helper_Acl();
$helper->setRoles();
$helper->setResources();
$helper->setPrivilages();
$helper->setAcl();

$front = Zend_Controller_Front::getInstance();
$front->registerPlugin(new Sess_Controller_Plugins_Acl);
$front->registerPlugin(new Sess_Controller_Plugins_Session);*/


$application->bootstrap();
$application->run();
    