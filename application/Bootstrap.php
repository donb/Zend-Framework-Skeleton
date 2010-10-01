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
 * @subpackage New_app_Application_Bootstrap
 */

/**
 * Setup and inititializations based on the Environment (Production, Staging, etc.)
 */
class Bootstrap
{
    /**
     * Creates a Bootstrap Object and Configures it based in the Configuration Context
     *
     * @param string $configSection
     */
    public function __construct($configSection) {
        $this->_init($configSection);
        $this->_initLocale();
        $this->_initCache();
        $this->_initZFDebug();
        $this->_initFlagFlippers();
    }
    
    /**
     * Initialize the object base in the Configuration Context
     *
     * @param string $configSection
     */
    private function _init($configSection) {
        // Calculate the pathes for the application and the main folder
        $appPath = dirname(__FILE__);
        $rootDir = dirname($appPath);
        
        // We define some constants using pathes and context variable
        define('ROOT_DIR', $rootDir);
        define('APP_PATH', $appPath);
        define('APP_ENVIRONMENT', $configSection);
        
        // Set up all the pathes needed for the application
        // 
        // Use the following code for every module
        // 
        // $pathToInclude .= PATH_SEPARATOR . ROOT_DIR . '/application/modules/<MODULE_NAME>/controllers';
        // $pathToInclude .= PATH_SEPARATOR . ROOT_DIR . '/application/modules/<MODULE_NAME>/models';
        $pathToInclude = get_include_path();
        $pathToInclude .= PATH_SEPARATOR . ROOT_DIR . '/library';
        $pathToInclude .= PATH_SEPARATOR . ROOT_DIR . '/application/modules';
        $pathToInclude .= PATH_SEPARATOR . ROOT_DIR . '/application/library';
        $pathToInclude .= PATH_SEPARATOR . ROOT_DIR . '/application/config';
        set_include_path($pathToInclude);
        
        //We set up Zend_Loader as the default autoload handler
        require_once 'Zend/Loader/Autoloader.php';
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(TRUE);
        
        // Load Configuration from INI Config File
        // The Zend_Config_Ini component will parse the ini file, and resolve all of
        // the values for the given section.  Here we will be using the section name
        // that corresponds to the APP's Environment
        Zend_Registry::set('configSection', $configSection);
        $config = new Zend_Config_Ini(APP_PATH . '/config/app.ini', $configSection);
        
        // Store in the registry all the info
        Zend_Registry::set('config', $config);
        Zend_Registry::set('ROOT_DIR', ROOT_DIR);
        Zend_Registry::set('APP_PATH', APP_PATH);
        Zend_Registry::set('APP_ENVIRONMENT', APP_ENVIRONMENT);
        
        // Register the version of the app
        if (isset($config->release->version)) {
            define('APP_VERSION', $config->release->version);
        }else{
            define('APP_VERSION', 'unknown');
        }
        
        Zend_Registry::set('APP_VERSION', APP_VERSION);
        
        // Zend_Db implements a factory interface that allows developers to pass in an
        // adapter name and some parameters that will create an appropriate database
        // adapter object.  In this instance, we will be using the values found in the
        // "database" section of the configuration obj.
        $dbAdapter = Zend_Db::factory($config->database);
        
        // Since our application will be utilizing the Zend_Db_Table component, we need
        // to give it a default adapter that all table objects will be able to utilize
        // when sending queries to the db.
        Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);
        Zend_Registry::set('dbAdapter', $dbAdapter);
        
        //Start the Zend Layout
        Zend_Layout::startMvc();
    }
    
    /**
     * This method prepares the Front Controller and dispatch the request
     *
     * @return void
     */
    public function runApp() {
        // The Zend_Front_Controller class implements the Singleton pattern, which is a
        // design pattern used to ensure there is only one instance of
        // Zend_Front_Controller created on each request.
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->throwExceptions(APP_ENVIRONMENT == Environments::$development);
        
        // Setup the ErrorHandler Plugin
        $frontController->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());
        
        // Module directory setup
        // Point the front controller to your module directory.
        // "frontend" is goinbg to be the default Module
        $frontController->addModuleDirectory(ROOT_DIR . '/application/modules');
        $frontController->setDefaultModule('example');
        
        // Registering all the frontController Plugins
        $config = Zend_Registry::get('config');
        
        // Application_Plugin_Logger
        $frontController->registerPlugin(new Application_Plugin_Logger());
        
        // Application_Plugin_FlashMessages
        $frontController->registerPlugin(new Application_Plugin_FlashMessages());
        
        // Application_Plugin_VersionHeader sends a X-SF header with the system version for debugging
        $frontController->registerPlugin(new Application_Plugin_VersionHeader());
        
        // Application_Controller_Plugin_LayoutSwitcher takes care of the layout we have to load
        $frontController->registerPlugin(new Application_Plugin_LayoutSwitcher());
        
        // Adding Action Helpers to Controllers
        
        // Logger Action Helper adds a helper to $this->_helpers list that give you
        // the possibility to log to Firebug. Example: $this->_helper->log('Message');
        Zend_Controller_Action_HelperBroker::addHelper(new Application_ActionHelper_Logger());
        
        $routerConfig = new Zend_Config_Xml(
            APP_PATH . '/config/routes.xml',
            APP_ENVIRONMENT
        );
        
        //Register the view helper path
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->initView();
        
        //add the global helper directory path
        $viewRenderer->view->addHelperPath(ROOT_DIR . '/library/Application/ViewHelpers');
        
        $router = $frontController->getRouter();
        $router->addConfig($routerConfig, 'routes');
        
        // Process the request
        $frontController->dispatch();
    }
    
    /**
     * Setup the locale based on the browser
     *
     * @return void
     */
    private function _initLocale(){
        if ($locale === null) {
            $locale = new Zend_Locale();
        }
        
        if (!Zend_Locale::isLocale($locale, true, false)) {
            if (!Zend_Locale::isLocale($locale, false, false)) {
                throw new Zend_Locale_Exception("The locale '$locale' is no known locale");
            }
            
            $locale = new Zend_Locale($locale);
        }
        
        if ($locale instanceof Zend_Locale) {
            Zend_Registry::set('Zend_Locale', $locale);
        }
    }
    
    /**
     * Initialize the ZFDebug Widget
     *
     * @return void
     */
    private function _initZFDebug(){
        $config = Zend_Registry::get('config');
        
        if(isset($config->zfdebug->enabled) && $config->zfdebug->enabled == TRUE){
            $dbAdapter = Zend_Registry::get('dbAdapter');
            
            $options = array(
                'plugins' => array('Variables',
                                   'Html',
                                   'Database' => array('adapter' => array('standard' => $dbAdapter)),
                                   'File' => array('basePath' => ROOT_DIR),
                                   'Memory',
                                   'Time',
                                   'Registry',
                                   //'Cache' => array('backend' => $cache->getBackend()),
                                   'Exception')
            );
            
            $debug = new ZFDebug_Controller_Plugin_Debug($options);
            
            $frontController = Zend_Controller_Front::getInstance();
            $frontController->registerPlugin($debug);
        }
    }
    
    /**
     * Initialize the ACL System
     *
     * @return void
     */
    private function _initFlagFlippers(){
        Application_FlagFlippers_Manager::load();
    }
    
    /**
     * Initialize the Cache System
     *
     * @return void
     */
    private function _initCache(){
        $manager = new Zend_Cache_Manager();
        
        //Cache with file as a backend
        $file = array(
            'frontend' => array(
                'name' => 'Core',
                'options' => array(
                    'lifetime' => Zend_Registry::get('config')->cache->file->lifetime,
                    'automatic_serialization' => TRUE
                )
            ),
            'backend' => array(
                'name' => 'File',
                'options' => array(
                    'cache_dir' => ROOT_DIR . '/cache/'
                )
            )
        );
        
        //Cache with memcache as a backend
        $memcache = array(
            'frontend' => array(
                'name' => 'Core',
                'options' => array(
                    'lifetime' => Zend_Registry::get('config')->cache->memcache->lifetime,
                    'automatic_serialization' => TRUE,
                    'caching' => Zend_Registry::get('config')->cache->enabled,
                    'logging' => Zend_Registry::get('config')->cache->logging,
                )
            ),
            'backend' => array(
                'name' => 'Memcached',
                'options' => array(
                    'servers' => Zend_Registry::get('config')->memcache->toArray(),
                )
            )
        );
        
        //Add the templates to the manager
        $manager->setCacheTemplate('file', $file);
        $manager->setCacheTemplate('memcache', $memcache);
        
        Zend_Registry::set('Zend_Cache_Manager', $manager);
    }
}