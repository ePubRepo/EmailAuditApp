<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('../emailarchive/inc_email_archive_bootstrap.php');

require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/inc_global_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_http_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_openid_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/controller/OpenIdOAuthResponseValueValidation.php');

require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/controller/IdentityCheckResponseController.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/IdentityCheckRequest.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/IdentityCheckResponse.php');

Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Beginning identity check response handling');

$myIdentityCheckResponse = new IdentityCheckResponse($_GET);

$myIdentityCheckResponseController = new IdentityCheckResponseController($myIdentityCheckResponse);
$is_valid = $myIdentityCheckResponseController->isIdentityValidated();

Logger::add_info_log_entry(__FILE__ . __LINE__ . ' identity check response of ' . (($is_valid === true) ? 'true' : 'false'));

if ($is_valid)
{
	if (isset($_GET['final_landing_url']))
	{
		header('Location: ' . urldecode($_GET['final_landing_url']));
	}
	else
	{
		header('Location: index.php');
	}
}
else
{
	header('Location: /emailarchive/welcome.php?mode=id_failed');
}
?>
