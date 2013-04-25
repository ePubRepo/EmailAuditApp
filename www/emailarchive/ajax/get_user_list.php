<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('../../../emailarchive/inc_email_archive_bootstrap.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_http_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_oauth_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/User.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/UserList.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/controller/UserListResponseParser.php');

$user_authorized = EmailArchiveAccessIdentityCheckController::checkAccessIdentityAndPerformRedirection(true);
if (!$user_authorized)
{
	$ajaxResponseVariables = array();
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_AUTHORIZATION_ERROR;
	echo json_encode($ajaxResponseVariables);
	die;
}

$arrReturnData = array();

try {
	$emailAddress = AccessIdentityController::getPurportedEmailFromHttpRequest();
	$domainName = GlobalFunctions::getDomainFromFullEmailAddress($emailAddress);
	$identitySecret = AccessIdentityController::getPurportedItentitySecretFromHttpRequest();
	$scope_needed = "https://apps-apis.google.com/a/feeds/user/";
	
	$myOauthRequest = new OAuth1Request(HTTPConstants::METHOD_GET, 'https://apps-apis.google.com/a/feeds/' . $domainName . '/user/2.0', OAuth1Request::OAUTH_AUTHENTICATION_METHOD_QUERYSTRING);
	$myOauthRequest = new OAuth1Request(HTTPConstants::METHOD_GET, 'https://apps-apis.google.com/a/feeds/user/2.0/' . $domainName . '', OAuth1Request::OAUTH_AUTHENTICATION_METHOD_QUERYSTRING);
	$myOauthRequest->setOauthToken(AccessIdentityController::getOauthAccessTokenFromFile($emailAddress, $scope_needed));
	$myOauthRequest->setOauthTokenSecret(AccessIdentityController::getOauthAccessTokenSecretFromFile($emailAddress, $scope_needed));

//	$myOauthRequest->setOauthToken(OAuthConstants::MARKETPLACE_APPLICATION_KEY);
//	$myOauthRequest->setOauthTokenSecret(OAuthConstants::MARKETPLACE_APPLICATION_SECRET);

// not sure why these were here; they do not seem to do anything
//	$myOauthRequest->overrideOauthConsumerKey(OAuthConstants::WEB_APPLICATION_CONSUMER_KEY);
//	$myOauthRequest->overrideOauthConsumerSecret(OAuthConstants::WEB_APPLICATION_CONSUMER_SECRET);
	$myOauthRequest->constructOauthRequest();
	
	$myRequest = new HTTPGetRequest();
	$myRequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
	$myRequest->setConnectionHost("apps-apis.google.com");
	$myRequest->setHttpHost("apps-apis.google.com");
	$myRequest->setPath("a/feeds/" . $domainName . "/user/2.0");
	$myRequest->setPath("a/feeds/user/2.0/" . $domainName);
	$myRequest->addHeader(new HTTPHeader('Accept', '*/*'));
	$myRequest->addHeader(new HTTPHeader('Authorization', $myOauthRequest->getFinalOauthAuthorizationHeader()));
	$myRequest->addHeader(new HTTPHeader('Content-Type', 'application/atom+xml'));
	$myRequest->addHeader(new HTTPHeader('GData-Version', '2.0'));
	$myRequest->executeRequest();
	
	$myUserListResponseParser = new UserListResponseParser($myRequest->getHttpResponse());
	$myUserList = $myUserListResponseParser->getUserList();
	
	$arrReturnData[AjaxConstants::RESPONSE_CONTENTS_VARIABLE] = array();
	$arrReturnData[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_SUCCESSFULLY;
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Successfully processed AJAX request for list of users, returning ' . count($arrReturnData[AjaxConstants::RESPONSE_CONTENTS_VARIABLE]) . ' users');
	foreach ($myUserList->getArrayOfUsers() as $user)
	{
		array_push($arrReturnData[AjaxConstants::RESPONSE_CONTENTS_VARIABLE], $user->getEmailAddress());
	}
}
catch (InvalidHttpAuthorization $e)
{
	$arrReturnData[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_AUTHORIZATION_ERROR;
	Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for list of users due to HTTP Authorization Error');
}
catch (DomainCannotUseApiException $e)
{
	$arrReturnData[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_DOMAIN_CANNOT_USE_API;	
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for list of users due to DomainCannotUseApiException');
}
catch (UserCannotUseApiException $e)
{
	$arrReturnData[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_USER_NOT_AUTHORIZED_API;
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for list of users due to UserCannotUseApiException');
}
catch (NoOAuthTokenRepository $e)
{
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_NO_OAUTH_TOKEN_REPOSITORY;	
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for export history due to no presence of OAuthTokenRepository for user');
}
catch (Exception $e)
{
	$arrReturnData[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_UNCAUGHT_ERROR;
	Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for list of users due to uncought exception');
}

echo json_encode($arrReturnData);
?>
