<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('../../emailarchive/inc_email_archive_bootstrap.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_http_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_oauth_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_openid_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/controller/OpenIdOAuthResponseValueValidation.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/controller/IdentityCheckResponseController.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/IdentityCheckRequest.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/IdentityCheckResponse.php');

// STEP 0: Check for domain variable
if (!isset($_GET['domain']) || !GlobalFunctions::validateFullDomainname($_GET['domain'])) {
	// OpenId flow failed
	Logger::add_warning_log_entry(__FILE__ . __LINE__ . ' OpenId flow failed as a valid domain name was not set.');
	header('Location: welcome.php?mode=id_failed');
	flush();
	die;
}

// NOTE: This is the page where the Google OneBar authentication occurs for the app;
// This needs to be entirely passive and it needs to record that the user should use the whitelisted OAuth credentials and not
// force the user to capture their own credentials

// STEP 1: PARSE OPENI RESPONSE
$myOpenIdResponse = new OpenIdResponse($_GET, $_POST);
$openIdOauthRequestToken = $myOpenIdResponse->getOpenIdResponseVariable('openid_ext2_request_token');
$openIdOauthFirstName = $myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_firstname');
$openIdOauthLastName = $myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_lastname');
$openIdOauthEmail = $myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_email');
$openIdClaimedId = $myOpenIdResponse->getOpenIdResponseVariable('openid_claimed_id');

// STEP 1.B: Determine Whether OpenId Flow Failed or the User Declined
$openIdMode = $myOpenIdResponse->getOpenIdResponseVariable('openid_mode');
if (isset($openIdMode)
	&& $openIdMode == "cancel") {
	// OpenId flow failed
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' OpenId flow canceled so ID process failed');
	header('Location: welcome.php?mode=id_failed');
	flush();
	die;
}

// STEP 1.C : Determine validity of responses
if (!OpenIdOAuthResponseValueValidation::validateEmailAddress($myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_email'))
	|| !OpenIdOAuthResponseValueValidation::validateFirstOrLastName($myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_firstname'))
	|| !OpenIdOAuthResponseValueValidation::validateFirstOrLastName($myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_lastname'))
	|| !OpenIdOAuthResponseValueValidation::validateClaimedId($myOpenIdResponse->getOpenIdResponseVariable('openid_claimed_id'))
   )
{
	// invalid OpenId response
	Logger::add_warning_log_entry(__FILE__ . __LINE__ . ' Invalid OpenIdOAuth repsonse variables; Email: ' . $myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_email') . ' // First Name: ' . $myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_firstname') . ' // Last Name: ' . $myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_lastname') . ' // Request Token: ' . $myOpenIdResponse->getOpenIdResponseVariable('openid_ext2_request_token') . ' // Claimed ID: ' . $myOpenIdResponse->getOpenIdResponseVariable('openid_claimed_id'));
	header('Location: welcome.php?mode=id_failed');
	flush();
	die;
}

// STEP 2: Simple Check Domain
// do a very simple plain vanilla domain check against email address
$domainNameFromQuerystring = $_GET['domain'];
$domainNameFromEmail = GlobalFunctions::getDomainFromFullEmailAddress($openIdOauthEmail);
if ($domainNameFromQuerystring != $domainNameFromEmail) {
	// OpenId flow failed
	Logger::add_warning_log_entry(__FILE__ . __LINE__ . ' OpenId flow failed as original domainname does not equal domainname in email address');
	header('Location: welcome.php?mode=id_failed');
	flush();
	die;
}

// STEP 3: Have IdentityCheckResponseController validate entry and update IdentityRepository entry
$myIdentityCheckResponse = new IdentityCheckResponse($_GET);
$myIdentityCheckResponseController = new IdentityCheckResponseController($myIdentityCheckResponse);
$is_valid = $myIdentityCheckResponseController->isIdentityValidated();

?>
