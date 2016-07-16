<?php
class Zend_View_Helper_ErrorMessage
{
    public function errorMessage($errorMessage)
    {
        $messages = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->setNamespace($errorMessage)->getMessages();
        return isset($messages[0]) ? $messages[0] : '';
    }
}
