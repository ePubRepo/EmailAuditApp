<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('../emailarchive/inc_email_archive_bootstrap.php');

require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/inc_global_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_http_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_openid_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/controller/OpenIdOAuthResponseValueValidation.php');

require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/IdentityCheckRequest.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/IdentityCheckResponse.php');

Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Beginning identity check flow');

if (isset($_GET['auth_mode'])
	&& $_GET['auth_mode'] == 'active')
{
	$auth_mode = 'active';
}
else
{
	$auth_mode = 'inactive';
}

$final_landing_url_str = '';
if (isset($_GET['final_landing_url'])
	&& strlen(urldecode($_GET['final_landing_url'])) > 0
	&& stripos(urldecode($_GET['final_landing_url']), 'www') === false
	&& stripos(urldecode($_GET['final_landing_url']), 'http') === false
	&& stripos(urldecode($_GET['final_landing_url']), 'javascript') === false)
{
	$final_landing_url_str = '?final_landing_url=' . (urldecode($_GET['final_landing_url']));
}
else if (isset($_GET['auth_source']) && isset($_GET['domain']))
{
	$final_landing_url_str = '?final_landing_url=/';
}
else
{
	Logger::add_warning_log_entry(__FILE__ . __LINE__ . ' Invalid landing page URL specified');
	$final_landing_url_str = '?final_landing_url=/';
}

/**
 * In order to provent potentially huge multi-login bug that would cause a lot of problems,
 * we need to completely wipe out Email and Identity Secret cookies
 */
Logger::add_info_log_entry(__FILE__ . __LINE__ . ' As part of identity check flow, for security and to avoid bugs, revoke all current cookies');
AccessIdentityController::revokeIdentitySecretCookie();
AccessIdentityController::revokeEmailCookie();

// provide logic for automated identity validation process that occurs when a user clicks on the Google one-bar
if (isset($_GET['auth_source']) && isset($_GET['domain'])
	&& $_GET['auth_source'] == 'onebar' && strlen($_GET['domain']) > 2 )
{
	$final_landing_url = 'https://www.apps-apps.info/emailarchive/onebar_id_return.php?domain=' . $_GET['domain'];
}
else
{
	$final_landing_url = 'https://www.apps-apps.info/identity_check_response.php' . $final_landing_url_str;
}

Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Final landing URL passed to openId check: ' . $final_landing_url);

$myCheckRequest = new IdentityCheckRequest($final_landing_url);
$session_url = $myCheckRequest->getIdentityCheckUrl();

/**
 * Determine the identity check authorization flow (i.e., should it be "active" [user must start the flow]
 * or "passive" [flow starts automatically])
 */
if ($auth_mode == 'active')
{
	// AUTH MODE IS "ACTIVE"
	// show user identity check start prompt
	$myPageMetaData = new PageMetaData();
	$myPageMetaData->addCssFile(FileVersioner::getPathToFile('/emailarchive/css/general.css'));
	$myPageMetaData->addJavaScriptFile(FileVersioner::getPathToFile('/emailarchive/js/prod/prod.js', true));
	echo $myPageMetaData->getPublicHtml();
	
	$myPageTopEmail = new EmailarchivePageTop();
	echo $myPageTopEmail->getPublicHtml();

	?>
	<div class="center-container">
		<div class="main-header"></div>
		<div class="main-body">
		<ul>
		<li><span class="span-header">&raquo; Identity Check</span><span class="span-content">For security reasons, we need to re-validate your identity with Google.</span></li>
		</ul>
		</div>
		<div class="main-right">
		<input type="button" value="Launch App" onclick="window.location='<?php echo $session_url ?>'; return false;" class="button launch-button" />
		</div>
	</div>
	<?php
	
	$myPageBottomEmailarchive = new EmailarchivePageBottom();
	echo $myPageBottomEmailarchive->getPublicHtml();
	
	$myPageBottomData = new PageBottomData();
	echo $myPageBottomData->getPublicHtml();
}
else
{
	// AUTH MODE IS "INACTIVE"
	// send user to identity authorization flow inactively (i.e., without any of their action)
	header('Location: ' . $session_url);
}

?>
