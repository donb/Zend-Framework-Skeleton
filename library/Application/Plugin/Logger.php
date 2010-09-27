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
 * @subpackage New_app_Library_Application_Plugin_Logger
 */

/**
 * Set up and put in the Registry the main logger and link the writers (Firebug, File, etc.)
 */
class Application_Plugin_Logger extends Zend_Controller_Plugin_Abstract
{
    /**
     * Main logger
     *
     * @var Object
     */
    private $logger;
    
    /**
     * All the writers to be registered in the logger
     * - Firebug (Not in Production)
     * - File (general.log)
     *
     * @var Objects
     */
    private $firebug_writer;
    private $file_writer;
    
    /**
     * Constructor
     */
    public function __construct(){
        // Create the PATH to the file to log
        $context = Zend_Registry::get('APP_ENVIRONMENT');
        $pathLog = Zend_Registry::get('ROOT_DIR') . '/log/app.log';
        
        // Create main logger object
        $this->logger = new Zend_Log();
        
        // If we are in Production we don't want to register
        // the Firebug Logger (Security Problems)
        if($context != Environments::$production) {
            $this->firebug_writer = new Zend_Log_Writer_Firebug();
            $this->logger->addWriter($this->firebug_writer);
        }
        
        // Create and register the logger to a file
        $this->file_writer = new Zend_Log_Writer_Stream($pathLog);
        $this->logger->addWriter($this->file_writer);
        
        // Put the Logger in the Registry with Zend_Log name
        Zend_Registry::set('Zend_Log', $this->logger);
    }
}
