<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('../../emailarchive/inc_email_archive_bootstrap.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_http_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_oauth_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_openid_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/controller/OpenIdOAuthResponseValueValidation.php');

// NOTE: This is the page where the ORIGINAL authentication occurs for the app; it included OAuth and OpenID and is different than the periodic identity check

// STEP 1: PARSE OPENID+OAUTH RESPONSE
$myOpenIdResponse = new OpenIdResponse($_GET, $_POST);
$openIdOauthRequestToken = $myOpenIdResponse->getOpenIdResponseVariable('openid_ext2_request_token');
$openIdOauthFirstName = $myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_firstname');
$openIdOauthLastName = $myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_lastname');
$openIdOauthEmail = $myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_email');
$openIdClaimedId = $myOpenIdResponse->getOpenIdResponseVariable('openid_claimed_id');

// STEP 1.B: Determine Whether OAuth||OpenId Flow Failed or the User Declined
$openIdMode = $myOpenIdResponse->getOpenIdResponseVariable('openid_mode');
if (isset($openIdMode)
	&& $openIdMode == "cancel") {
	// OAuth||OpenId OAuth flow failed
	header('Location: welcome.php?mode=id_failed');
	flush();
	die;
}

// STEP 1.C: OAuth||OpenId Flow Succeeded, Check Validity of Response Variables
if (!OpenIdOAuthResponseValueValidation::validateEmailAddress($myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_email'))
	|| !OpenIdOAuthResponseValueValidation::validateFirstOrLastName($myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_firstname'))
	|| !OpenIdOAuthResponseValueValidation::validateFirstOrLastName($myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_lastname'))
	|| !OpenIdOAuthResponseValueValidation::validateRequestToken($myOpenIdResponse->getOpenIdResponseVariable('openid_ext2_request_token'))
	|| !OpenIdOAuthResponseValueValidation::validateClaimedId($myOpenIdResponse->getOpenIdResponseVariable('openid_claimed_id'))
   )
{
	// invalid OpenId response
	Logger::add_warning_log_entry(__FILE__ . __LINE__ . ' Invalid OpenIdOAuth repsonse variables; Email: ' . $myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_email') . ' // First Name: ' . $myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_firstname') . ' // Last Name: ' . $myOpenIdResponse->getOpenIdResponseVariable('openid_ext1_value_lastname') . ' // Request Token: ' . $myOpenIdResponse->getOpenIdResponseVariable('openid_ext2_request_token') . ' // Claimed ID: ' . $myOpenIdResponse->getOpenIdResponseVariable('openid_claimed_id'));
	header('Location: welcome.php?mode=id_failed');
	flush();
	die;
}

$openIdOauthRequestedScopes = $myOpenIdResponse->getOpenIdRequestedOAuthScopes();

// STEP 2: MAKE OAUTH HTTP REQUEST TO GET AUTHORIZED ACCESS TOKEN
$myOauthRequest = new OAuth1Request(HTTPConstants::METHOD_GET, 'https://www.google.com/accounts/OAuthGetAccessToken', OAuth1Request::OAUTH_AUTHENTICATION_METHOD_QUERYSTRING);
$myOauthRequest->overrideOauthConsumerKey(OAuthConstants::getWebApplicationConsumerKey());
$myOauthRequest->overrideOauthConsumerSecret(OAuthConstants::getWebApplicationConsumerSecret());
$myOauthRequest->setOauthToken($openIdOauthRequestToken);
$myOauthRequest->constructOauthRequest();

$authroizationRequestUrl = $myOauthRequest->getFinalFullRequestUrl();
$arrAuthroizationRequestUrl = parse_url($authroizationRequestUrl);

$myRequest = new HTTPGetRequest();
$myRequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
$myRequest->setConnectionHost($arrAuthroizationRequestUrl['host']);
$myRequest->setHttpHost($arrAuthroizationRequestUrl['host']);
$myRequest->setPath($arrAuthroizationRequestUrl['path'] . "?" . $arrAuthroizationRequestUrl['query']);
$myRequest->executeRequest();

// STEP 3: PARSE OAUTH AUTHORIZED ACCESS TOKEN RESPONSE
$myOauthAccessTokenResponse = new OAuthAccessTokenResponse($myRequest->getHttpResponse());
$access_token = $myOauthAccessTokenResponse->getOAuthToken();
$access_token_secret = $myOauthAccessTokenResponse->getOAuthTokenSecret();

// STEP 4: STORE USER DATA
$myIdentity = new Identity();
$myIdentity->setIdentityVariable(IdentityConstants::FIRST_NAME, $openIdOauthFirstName);
$myIdentity->setIdentityVariable(IdentityConstants::LAST_NAME, $openIdOauthLastName);
$myIdentity->setIdentityVariable(IdentityConstants::EMAIL_ADDRESS, $openIdOauthEmail);
$myIdentity->setIdentityVariable(IdentityConstants::CLAIMED_ID, $openIdClaimedId);
$myIdentity->setIdentityVariable(IdentityConstants::LAST_IDENTITY_VALIDATED_TIMESTAMP, mktime());

$myTokenRepository = new OAuthTokenRepository();
foreach ($openIdOauthRequestedScopes as $scope)
{
	$myToken = new OAuthToken();
	$myToken->setOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN, $access_token);
	$myToken->setOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SECRET, $access_token_secret);
	$myToken->setOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SCOPE, $scope);
	$myToken->setOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_TIMESTAMP, mktime());
	$myTokenRepository->addOAuthToken($myToken);
}

$myHelper = new IdentityRepositoryHelper();
$myHelper->setIdentity($myIdentity);
$myHelper->setOAuthTokenRepository($myTokenRepository);
$myHelper->storeIdentityRepositoryToDisk();

header('Location: index.php');
?>
