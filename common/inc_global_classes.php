<?php 
// Copyright 2011. Eric Beach. All Rights Reserved.

//Note: The order of these inclides is important and changing them can cause serious consequences
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/inc_global_exceptions.php');

require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/controller/Logger.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/controller/FileVersioner.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/controller/AccessIdentityController.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/controller/DevelopmentModeController.php');

if (DevelopmentModeController::showDevelopmentMode())
{
	ini_set('display_errors', '1');
	error_reporting(E_ALL);
}
else
{
	ini_set('display_errors', '0');
	error_reporting(0);
}


require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/controller/IdentityOAuthManagement.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/view/PageBottomData.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/view/PageMetaData.php');

Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Successfully executed through the end of the Global Classes Bootstrap');
?>
