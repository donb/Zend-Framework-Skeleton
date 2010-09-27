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
 * @subpackage New_app_Application_Plugin_VersionHeader
 */
 
/**
 * Pushes release version information through a special X-Version header
 */
class Application_Plugin_VersionHeader extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request){
        $version = Zend_Registry::get('APP_VERSION');
        
        if (!headers_sent()) {
            header('X-Version: ' . $version);
        }
    }
}
