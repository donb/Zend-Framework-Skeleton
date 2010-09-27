<?php

class Application_Plugin_LayoutSwitcher extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request){
        if (strcmp($request->getModuleName(), 'default') !== 0) {
            $moduleLayoutPath = Zend_Controller_Front::getInstance()->getModuleDirectory() . '/layouts/scripts';
            if (file_exists($moduleLayoutPath . '/layout.phtml')) {
                Zend_Layout::getMvcInstance()->setLayoutPath($moduleLayoutPath);
            }
        } else {
            $applicationLayoutPath = APPLICATION_PATH . '/layouts/scripts';
            if (file_exists($applicationLayoutPath . '/layout.phtml')) {
                $layout = Zend_Layout::getMvcInstance()->setLayoutPath($applicationLayoutPath);
            }
        }
    }
}