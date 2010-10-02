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
class Example_ExampleController extends AppController
{
    /**
     * Principal action
     *
     * @return void
     */
    public function indexAction(){
        echo $this->_helper->flagFlippers('christopher', 'index') ? 'allowed' : 'denied';
    }
}