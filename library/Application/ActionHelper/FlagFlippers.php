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
 * @subpackage New_app_Library_Application_ActionHelper_Logger
 */

/**
 * Take the main logger and put it as a Action Helper
 * 
 * This helper is accessible from controllers via $this->_helper->flagFlippers()
 */
class Application_ActionHelper_FlagFlippers extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * This method is called automatically when using the name of the helper directly
     *
     * @param string $role 
     * @param string $resource
     * @return boolean
     */
    public function direct($role, $resource) {
        return Application_FlagFlippers_Manager::isAllowed($role, $resource);
    }
}