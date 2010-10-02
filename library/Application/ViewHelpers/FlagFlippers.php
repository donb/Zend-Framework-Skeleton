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
 * Flag and Flippers view helper
 * 
 * This helper is provided to be able to check the permissions
 * over flag and flippers for the users in order to modify the views.
 */
class Zend_View_Helper_FlagFlippers extends Zend_View_Helper_Abstract{
    
    /**
     * Check the permissions of a role through flag and flippers
     *
     * @param string $role
     * @param string $resource
     * @return boolean
     */
    public function flagFlippers($role, $resource) {
        return Application_FlagFlippers_Manager::isAllowed($role, $resource);
    }
}