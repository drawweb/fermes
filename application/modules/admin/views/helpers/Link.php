<?php
class Zend_View_Helper_Link extends Zend_View_Helper_Url
{

    public function link($controllerName = null, $actionName = null, $moduleName = null, $params = '', $name = 'default', $reset = true)
    {
        if ($controllerName === null) {
            $controllerName = Zend_Controller_Front::getInstance()->getRequest()->getParam('controller');
        }
        if ($actionName === null) {
            $actionName = Zend_Controller_Front::getInstance()->getRequest()->getParam('action');
        }
        if (is_array($params)) {
            $params = '?' . http_build_query($params);
        }
        return parent::url(array(
        'controller'=> $controllerName,
        'action'    => $actionName,
        'module'    => $moduleName), $name, $reset) . $params;
    }
}
