<?php

class ErrorController extends Zend_Controller_Action
{
    
public function errorAction()
    {
    	$errors = $this->_getParam('error_handler');

	   $mail = new Zend_Mail();
	   $resultat = $mail->setBodyHtml('Exception : <br/><br/>'.htmlentities($errors->exception).'<br/><br/> Request : <br/><br/>'.htmlentities($errors->request).'')
	    	->setFrom('contact@draw-web.fr', 'Erreur - WINE idea')
	    	->addTo('contact@draw-web.fr', 'Erreur - WINE idea')
	    	->setSubject('Erreur sur le site wineidea.fr')
	    	->send();  
    }
    
    public function privilegesAction()
    {
    	
    }
}
