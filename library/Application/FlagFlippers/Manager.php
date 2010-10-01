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
 * Handle different operations with the ACL and the functionality deploy
 */
class Application_FlagFlippers_Manager
{
    public static $indexKey = 'FlagFlippers';
    
    /**
     * Load the ACL to the Registry if is not there
     * 
     * This function takes care about generating the acl from the permission
     * scheme file if the info is not in the registry and memcache.
     * 
     * If the acl is inside memcache we load it from there.
     * 
     * @return void
     */
    public static function load(){
        if(!Application_FlagFlippers_Manager::_checkIfExist()){
            if(!$acl = Application_FlagFlippers_Manager::_getFromMemcache()){
                $acl = Application_FlagFlippers_Manager::_generateFromPermissionSchemeFile();
                Application_FlagFlippers_Manager::_storeInMemcache($acl);
            }
            
            Application_FlagFlippers_Manager::_storeInRegistry($acl);
        }
    }
    
    /**
     * Save the Acl to memcache and export the config to a xml file
     *
     * @return boolean
     */
    public static function save(){
        if(Application_FlagFlippers_Manager::_checkIfExist()){
            Application_FlagFlippers_Manager::_exportPermissionSchemeToFile();
            Application_FlagFlippers_Manager::_storeInMemcache();
        }else{
            throw new Exception('To be able to save an ACL first have to be inside the Zend_Registry');
        }
    }
    
    public function isAllowed($role, $resource){
        return Application_FlagFlippers_Manager::_getFromRegistry()->isAllowed($role, $resource);
    }
    
    /**
     * Check if the acl exists in the Zend_Registry
     *
     * @return boolean
     */
    private function _checkIfExist(){
        return Zend_Registry::isRegistered(Application_FlagFlippers_Manager::$indexKey);
    }
    
    /**
     * Get the Acl from the Registry
     *
     * @return void
     */
    private static function _getFromRegistry(){
        if(Application_FlagFlippers_Manager::_checkIfExist()){
            return Zend_Registry::get(Application_FlagFlippers_Manager::$indexKey);
        }
        
        return FALSE;
    }
    
    /**
     * Retrieve the acl from memcache
     *
     * @return Zend_Acl | boolean
     */
    private static function _getFromMemcache(){
        $cacheHandler = Zend_Registry::get('Zend_Cache_Manager')->getCache('memcache');
        
        if($acl = $cacheHandler->load(Application_FlagFlippers_Manager::$indexKey)){
            return $acl;
        }
        
        return FALSE;
    }
    
    /**
     * Generate the Acl object from the permission file
     *
     * @return Zend_Acl
     */
    private static function _generateFromPermissionSchemeFile(){
        $acl = new Zend_Acl();
        
        $xml = new DOMDocument();
        $xml->load(ROOT_DIR . '/application/config/flagflippers-scheme.xml');
        
        //Add the roles
        foreach($xml->getElementsByTagName('role') as $role){
            //Check if the role has inheritance
            $inheritance = array();
            $parents = $xml->getElementsByTagName('parents');
            if($parents){
                foreach($parents as $inherit){
                    if($inherit->getAttribute('role') == $role->nodeValue){
                        $inheritedFrom = $inherit->getElementsByTagName('inherit');
                        foreach($inheritedFrom as $i){
                            $inheritance[] = $i->nodeValue;
                        }
                    }
                }
            }
            
            $acl->addRole(new Zend_Acl_Role($role->nodeValue), $inheritance);
        }
        
        //Get the resources
        foreach($xml->getElementsByTagName('resource') as $resource){
            $acl->add(new Zend_Acl_Resource($resource->nodeValue));
        }
        
        //Get the permissions
        foreach($xml->getElementsByTagName('flipper') as $flipper){
            $role = $flipper->getAttribute('role');
            $flags = $flipper->getElementsByTagName('flag');
            
            foreach($flags as $flag){
                $resource = $flag->getAttribute('resource');
                $allow = (bool) $flag->getAttribute('allow');
                
                if($allow){
                    $acl->allow($role, $resource);
                }else{
                    $acl->deny($role, $resource);
                }
            }
        }
        
        return $acl;
    }
    
    /**
     * Store the Acl in memcache
     *
     * @return void
     */
    private static function _storeInMemcache($acl = NULL){
        if(is_null($acl) && Application_FlagFlippers_Manager::_checkIfExist()){
            $acl = Application_FlagFlippers_Manager::_getFromRegistry();
        }
        
        if(empty($acl)){
            throw new Exception('You must provide a valid Acl in order to store it');
        }
        
        $cacheHandler = Zend_Registry::get('Zend_Cache_Manager')->getCache('memcache');
        
        $cacheHandler->save($acl, Application_FlagFlippers_Manager::$indexKey);
    }
    
    /**
     * Store the Acl in the Registry
     *
     * @return void
     */
    private static function _storeInRegistry($acl){
        Zend_Registry::set(Application_FlagFlippers_Manager::$indexKey, $acl);
    }
    
    /**
     * Export the current Acl to a xml permission scheme file
     *
     * @return void
     */
    private static function _exportPermissionSchemeToFile(){
        if(Application_FlagFlippers_Manager::_checkIfExist()){
            $acl = Application_FlagFlippers_Manager::_getFromRegistry();
            
            //Get the roles and resources
            $roles = $acl->getRoles();
            $resources = $acl->getResources();
            
            //Compute the direct parents of each role
            foreach($roles as $role){
                foreach($roles as $parent){
                    if($acl->inheritsRole($role, $parent, TRUE)){
                        $parents[$role][] = $parent;
                    }
                }
            }
            
            //Compute the access of each role for each resource
            foreach($roles as $role){
                foreach($resources as $resource){
                    $permissions[$role][] = array($resource, $acl->isAllowed($role, $resource));
                }
            }
            
            //Generate the XML file to store the permissions
            $doc = new DOMDocument('1.0', 'UTF-8');
            $doc->preserveWhiteSpace = FALSE;
            $doc->formatOutput = TRUE;
            
            $root = $doc->createElement('permissions');
            $doc->appendChild($root);
            
            $rolesNode = $doc->createElement('roles');
            $root->appendChild($rolesNode);
            
            $resourcesNode = $doc->createElement('resources');
            $root->appendChild($resourcesNode);
            
            $flippersNode = $doc->createElement('flippers');
            $root->appendChild($flippersNode);
            
            $inheritanceNode = $doc->createElement('inheritance');
            $root->appendChild($inheritanceNode);
            
            //Save the roles
            if($roles){
                foreach($roles as $role){
                    $rolesNode->appendChild($doc->createElement('role', $role));
                }
            }
            
            //Save the resources
            if($resources){
                foreach($resources as $resource){
                    $resourcesNode->appendChild($doc->createElement('resource', $resource));
                }
            }
            
            //Save the permissions
            if($permissions){
                foreach($permissions as $role => $flippers){
                    $flipperNode = $doc->createElement('flipper');
                    $flippersNode->appendChild($flipperNode);
                    
                    $roleAttr = $doc->createAttribute('role');
                    $flipperNode->appendChild($roleAttr);
                    
                    $roleAttr->appendChild($doc->createTextNode($role));
                    
                    foreach($flippers as $flipper){
                        $flagNode = $doc->createElement('flag');
                        $flipperNode->appendChild($flagNode);
                        
                        $resourceAttr = $doc->createAttribute('resource');
                        $flagNode->appendChild($resourceAttr);
                        
                        $resourceAttr->appendChild($doc->createTextNode($flipper[0]));
                        
                        $allowAttr = $doc->createAttribute('allow');
                        $flagNode->appendChild($allowAttr);
                        
                        $allowAttr->appendChild($doc->createTextNode((int) $flipper[1]));
                    }
                }
            }
            
            //Save the parent inheritance
            if($parents){
                foreach($parents as $role => $parent){
                    $parentNode = $doc->createElement('parents');
                    $inheritanceNode->appendChild($parentNode);
                    
                    $roleAttr = $doc->createAttribute('role');
                    $parentNode->appendChild($roleAttr);
                    
                    $roleAttr->appendChild($doc->createTextNode($role));
                    
                    foreach($parent as $p){
                        $inheritNode = $doc->createElement('inherit', $p);
                        $parentNode->appendChild($inheritNode);
                    }
                }
            }
            
            //Open the permissions file
            $f = fopen(ROOT_DIR . '/application/config/flagflippers-scheme.xml', 'w+');
            
            if($f){
                fwrite($f, $doc->saveXML());
                fclose($f);
            }else{
                throw new Exception('The flagflippers-scheme.xml file cannot be created or writed');
            }
        }else{
            throw new Exception('To export the permissions to a file the Acl must be on the Zend_Registry');
        }
    }
}