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
 * @package    New_app_Application
 * @subpackage New_app_Application_Modules_Example_Controllers_Example
 */

/**
 * Example Controller
 */
class AppController extends Zend_Controller_Action{
    
    /**
     * Initialize the shared functions
     *
     * @return void
     */
    public function init(){
        $this->_initTranslationSystem();
    }
    
    /**
     * Init the translation system and set the route and the lang we have to use
     *
     * @return void
     */
    private function _initTranslationSystem(){
        $config = Zend_Registry::get('config');
        
        //Extract some info from the request
        $module = $this->getRequest()->getModuleName();
        $lang = Zend_Registry::get('Zend_Locale')->getLanguage();
        
        //Create a zend_log for missing translations
        $pathLog = Zend_Registry::get('ROOT_DIR') . '/log/missing_translations/' . date('Ymd') . '_' . $lang . '.log';
        $writer = new Zend_Log_Writer_Stream($pathLog);
        $logger = new Zend_Log($writer);
        
        $translate = new Zend_Translate(
            array(
                'adapter' => 'gettext',
                'content' => APP_PATH . '/modules/' . $module . '/translations/' . $lang . '.mo',
                'locale'  => $lang,
                'disableNotices' => $config->translations->disable_notices,
                'log' => $logger,
                'logMessage' => "Missing translation: %message%",
                'logUntranslated' => TRUE
            )
        );
        
        Zend_Registry::set('Zend_Translate', $translate);
    }
}