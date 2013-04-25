<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('/home/appsappsinfo/common/inc_global_constants.php');

require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/inc_global_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'emailarchive/AjaxConstants.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'emailarchive/classes/view/EmailarchivePageTop.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'emailarchive/classes/view/EmailarchivePageBottom.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'emailarchive/classes/controller/EmailArchiveAccessIdentityCheckController.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_http_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_oauth_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_openid_classes.php');

Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Successfully executed through the end of the Email Archive Bootstrap');
?>
