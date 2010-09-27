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
 * @subpackage New_app_Application_Modules_Example_Models_Example
 */
class Example extends Zend_Db_Table_Abstract
{
    //Name of the table
    protected $_name = 'example';
    
    protected $_sequence = FALSE;
}