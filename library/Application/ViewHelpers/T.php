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
 * Translation view helper (just a shortcut for translate)
 */
class Zend_View_Helper_T extends Zend_View_Helper_Abstract{
    
    /**
     * Translate a message
     * You can give multiple params or an array of params.
     * If you want to output another locale just set it as last single parameter
     * Example 1: translate('%1\$s + %2\$s', $value1, $value2, $locale);
     * Example 2: translate('%1\$s + %2\$s', array($value1, $value2), $locale);
     *
     * @param  string $messageid Id of the message to be translated
     * @return string|Zend_View_Helper_Translate Translated message
     */
    public function t($messageid) {
        if ($messageid === null) {
            return $this;
        }
        
        $translate = Zend_Registry::get('Zend_Translate');
        $options   = func_get_args();
        
        array_shift($options);
        $count  = count($options);
        $locale = null;
        if ($count > 0) {
            if (Zend_Locale::isLocale($options[($count - 1)], null, false) !== false) {
                $locale = array_pop($options);
            }
        }
        
        if ((count($options) === 1) and (is_array($options[0]) === true)) {
            $options = $options[0];
        }
        
        if ($translate !== null) {
            $messageid = $translate->translate($messageid, $locale);
        }
        
        if (count($options) === 0) {
            return $messageid;
        }
        
        return vsprintf($messageid, $options);
    }
}