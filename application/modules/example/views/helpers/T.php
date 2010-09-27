<?php

class Zend_View_Helper_T {
    
    public function t($messageId) {
        $options = func_get_args();
        array_shift($options);
        
        return Zend_Registry::get('Zend_Translate')->translate($messageId, $options);
    }
}