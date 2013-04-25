<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('../../../emailarchive/inc_email_archive_bootstrap.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_http_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_oauth_classes.php');

$user_authorized = EmailArchiveAccessIdentityCheckController::checkAccessIdentityAndPerformRedirection(true);
if (!$user_authorized)
{
	$ajaxResponseVariables = array();
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_AUTHORIZATION_ERROR;
	echo json_encode($ajaxResponseVariables);
	die;
}

// example GET url: https://www.apps-apps.info/emailarchive/ajax/download_exported_mailbox.php?fileUrl=https://apps-apis.google.com/a/data/compliance/audit/OQAAAFp0atTmS3OqsUe10RKJ7BRlz_v-9qikwHCrJq_77lVwpnH-T7LfLHa_W_JJNOnu2sKpCBNPDeWxeb5XGJ5g2tMAlbTyAxoc25U9-UcPYAdaaIlgo5-wgkTc&requestId=45567124&requestEmailAddress=jason.test@apps-email.info&requestDate=2012-01-24-19-30

if (!isset($_GET['fileUrl'])
	|| !isset($_GET['requestId'])
	|| strlen($_GET['fileUrl']) < 3
	|| strlen($_GET['requestId']) < 3
	|| strlen($_GET['requestEmailAddress']) < 3
	|| strlen($_GET['requestDate']) < 3
	|| strlen($_GET['requestId']) < 3
	|| !GlobalFunctions::validateFullEmailaddress($_GET['requestEmailAddress'])
	|| !is_numeric($_GET['requestId'])
	|| preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}/', $_GET['requestDate']) != 1
	|| !preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $_GET['fileUrl'])
	)
	
{
	invalidInputs();
}
else
{
	deliverMailbox();
}

function deliverMailbox()
{
	$fileUrl = urldecode($_GET['fileUrl']);
	$urlparts = parse_url($fileUrl);
	$fileUrlPath = substr($urlparts['path'], 1);
	$requestTimestamp = $_GET['requestDate'];
	$requestEmailAddress =  $_GET['requestEmailAddress'];
	
	$absolutePathToEncryptedFile = GlobalConstants::getAbsolutePathToEmailArchiveRootDirectory() . GlobalConstants::getMailboxFoldername() . $requestEmailAddress . $requestTimestamp;
	$absolutePathToUnencryptedFile = $absolutePathToEncryptedFile . ".unencrypted.txt";
	
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Absolute Path to Encrypted Mailbox File: ' . $absolutePathToEncryptedFile);
	if (!file_exists($absolutePathToEncryptedFile))
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Encrypted Mailbox File Does Not Exist on Disk; Proceed to Fetch It');
		$emailAddress = AccessIdentityController::getPurportedEmailFromHttpRequest();
		$domainName = GlobalFunctions::getDomainFromFullEmailAddress($emailAddress);
		$identitySecret = AccessIdentityController::getPurportedItentitySecretFromHttpRequest();
		$scope = "https://apps-apis.google.com/a/feeds/user/";
		
		$myOauthRequest = new OAuth1Request(HTTPConstants::METHOD_GET, 'https://apps-apis.google.com/' . $fileUrlPath, OAuth1Request::OAUTH_AUTHENTICATION_METHOD_QUERYSTRING);
		$myOauthRequest->setOauthToken(AccessIdentityController::getOauthAccessTokenFromFile($emailAddress, $scope));
		$myOauthRequest->setOauthTokenSecret(AccessIdentityController::getOauthAccessTokenSecretFromFile($emailAddress, $scope));

// not sure why this is here; removing it seems to do nothing
//		$myOauthRequest->overrideOauthConsumerKey(OAuthConstants::WEB_APPLICATION_CONSUMER_KEY);
//		$myOauthRequest->overrideOauthConsumerSecret(OAuthConstants::WEB_APPLICATION_CONSUMER_SECRET);
		$myOauthRequest->constructOauthRequest();
			
		$myRequest = new HTTPGetRequest();
		$myRequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
		$myRequest->setConnectionHost("apps-apis.google.com");
		$myRequest->setHttpHost("apps-apis.google.com");
		$myRequest->setPath($fileUrlPath);
		$myRequest->addHeader(new HTTPHeader('Accept', '*/*'));
		$myRequest->addHeader(new HTTPHeader('Authorization', $myOauthRequest->getFinalOauthAuthorizationHeader()));
		$myRequest->addHeader(new HTTPHeader('Content-Type', 'application/atom+xml'));
		$myRequest->addHeader(new HTTPHeader('GData-Version', '2.0'));
		$myRequest->executeRequest();
		
		$fh = fopen($absolutePathToEncryptedFile, 'w') or die("can't open file");
		$stringDataToWrite = $myRequest->getHttpResponse()->getResponseContent();
		$is_successful = fwrite($fh, $stringDataToWrite);
		if ($is_successful)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Downloaded from GApps server and successfully wrote mailbox to file: ' . $absolutePathToEncryptedFile);
		}
		else
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Failed to write mailbox to file: ' . $absolutePathToEncryptedFile);
		}
		fclose($fh);
	}
	else
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Encrypted Mailbox File Does Exists on Disk; Do Not Make HTTP Request to Fetch It From the Web');
	}
	
	if (!file_exists($absolutePathToUnencryptedFile))
	{
		$commandToExecute = GlobalConstants::getAbsolutePathToEmailArchiveRootDirectory() . GlobalConstants::getBinDirectory() . 'decrypt_mailbox_and_write_it.sh "' . GlobalFunctions::getDomainFromFullEmailAddress($requestEmailAddress) . '" "' . $absolutePathToEncryptedFile . '"';
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Unencrypted Mailbox Does Not Exist at location: ' . $absolutePathToUnencryptedFile . ' // Need to Decrypt');
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Bash Command to Execute to Decrypt Encrypted Mailbox: ' . $commandToExecute);
		$execReturn = exec($commandToExecute, $output, $return_var);
	}
	else
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Unencrypted Mailbox Does Exist; No Need to Decrypt');
	}
	    
	// Read the file from disk
	if (!file_exists($absolutePathToUnencryptedFile))
	{
		// File does not exist or cannot be read
		Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Unable to find unencrypted mailbox file at location: ' . $absolutePathToUnencryptedFile);
		echo '<p>An error occurred on Google\'s end and the mailbox file appears to be corrupted. Please <a href="../">reload the homepage</a> of the Email Archive App and re-queue the download.';
	}
	else
	{
		// File exists on disk and can be read and returned
		// Set headers
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=" . basename($absolutePathToUnencryptedFile));
		header("Content-Type: text/plain");
		header("Content-Transfer-Encoding: binary");
		readfile($absolutePathToUnencryptedFile);
	}
}

function invalidInputs()
{
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Download of exported mailbox failed as the required $_GET variables were not specified');
	echo '<p>An error occurred and we do not have enough information to process your request. Please <a href="../">reload the homepage</a> of the Email Archive App.';
}
?>
