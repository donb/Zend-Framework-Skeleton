Description
===========

This is a Zend Framework Skeleton, below you'll find some specs about it.

* Zend Framework
* Module structure
* ZFDebug
* TDD ready
* PHPUnit ready
* Zend_Log configured (using firebug in development)
* Zend_Translate configured (using .mo files)
* Zend_Translate configured to log the missing translations in dev environment
* FlashMessages plugin installed
* LayoutSwitcher plugin installed
* VersionHeader plugin installed (send the version of the app through a special header)
* DBAdapter already configured (just change the credentials on the config.ini)
* Autoloader configured
* Router configured to read the routes from the file routes.xml
* Zend_Locale configured to detect the locale and degrades gracefully
* App configured to use three environments (dev, staging, production)
* Zend_Registry up and running
* All the handy data stored in the Registry like environment, some paths (app path, root path), config object...
* Custom error page
* View Helper to translate using the following method $this->t() instead of $this->translate() (much shorter)

Installation
============

1. Get a copy of the files in your machine
2. Create two folders called cache and log in the root of the project and a subfolder in log called missing_translations
    
    `On *nix:  mkdir -p cache log/missing_translations`
    
4. Give read/write access to those folders
    
    `On *nix: chmod -R 777 cache log`

That's it you can start now using the skeleton to build the next amazing app!
