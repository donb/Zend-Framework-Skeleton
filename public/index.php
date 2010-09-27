<?php
/**
 * New_app
 *
 * Copyright (C) 2010 Company. All rights reserved.
 *
 * Proprietary code. No modification, distribution or reproduction without
 * written permission.
 */

include('../application/Bootstrap.php');
$configSection = getenv('APP_ENVIRONMENT') ? getenv('APP_ENVIRONMENT') : 'development';
$bootstrap = new Bootstrap($configSection);
$bootstrap->runApp();
