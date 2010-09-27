<?php
/**
 * New_app
 *
 * Copyright (C) 2010 Company. All rights reserved.
 *
 * Proprietary code. No modification, distribution or reproduction without
 * written permission.
 *
 * @category   New_app
 * @package    New_app_Library
 * @subpackage New_app_Library_Application_Plugin_FlashMessenger
 */
class Application_Plugin_FlashMessages extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request){
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        
        $view = $viewRenderer->view;
        
        if ($flash->hasMessages()) {
            $view->flashMessages = $flash->getMessages();
        } else {
            $view->flashMessages = NULL;
        }
    }
}