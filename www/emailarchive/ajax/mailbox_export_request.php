<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('../../../emailarchive/inc_email_archive_bootstrap.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_http_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_oauth_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'emailarchive/classes/controller/KeyManagementController.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'emailarchive/classes/controller/CreateMailboxExportRequestController.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'emailarchive/classes/model/MailboxExportStatus.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'emailarchive/classes/model/MailboxExportStatusList.php');

$user_authorized = EmailArchiveAccessIdentityCheckController::checkAccessIdentityAndPerformRedirection(true);

if (!$user_authorized)
{
	$ajaxResponseVariables = array();
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_AUTHORIZATION_ERROR;
	echo json_encode($ajaxResponseVariables);
	die;
}

if (!isset($_GET['email_address']))
{
	Logger::add_warning_log_entry(__FILE__ . __LINE__ . ' Mailbox export request failed as there was no email access specified in the $_GET variable');
	$ajaxResponseVariables = array();
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_INVALID_USER;
	echo json_encode($ajaxResponseVariables);
	die;
}

if (!GlobalFunctions::validateFullEmailAddress($_GET['email_address']))
{
	Logger::add_warning_log_entry(__FILE__ . __LINE__ . ' Mailbox export request failed as there was no valid email access specified in the $_GET variable');
	$ajaxResponseVariables = array();
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_INVALID_USER;
	echo json_encode($ajaxResponseVariables);
	die;
}

$ajaxResponseVariables = array();
try {
	$requestController = new CreateMailboxExportRequestController($_GET['email_address']);
	$requestStatus = $requestController->getRequestStatus();

	$ajaxResponseVariables[AjaxConstants::RESPONSE_CONTENTS_VARIABLE]['emailaddress_to_export'] = $_GET['email_address'];
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_SUCCESSFULLY;
	
	if ($requestStatus == CreateMailboxExportRequestController::REQUEST_STATUS_SUCCESS)
	{
		// Successfully created mailbox export request for $_GET['email_address']
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Mailbox export request succeeded for user: ' . $_GET['email_address']);
		$ajaxResponseVariables[AjaxConstants::RESPONSE_CONTENTS_VARIABLE]['export_status_request'] = CreateMailboxExportRequestController::REQUEST_STATUS_SUCCESS;
	}
	else if ($requestStatus == CreateMailboxExportRequestController::REQUEST_STATUS_DUPLICATE)
	{
		// There is already a pending mailbox export request for $_GET['email_address']
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Mailbox export request not processed as it was a duplicate for user: ' . $_GET['email_address']);
		$ajaxResponseVariables[AjaxConstants::RESPONSE_CONTENTS_VARIABLE]['export_status_request'] = CreateMailboxExportRequestController::REQUEST_STATUS_DUPLICATE;
	}
	else
	{
		// Mailbox export request for $_GET['email_address'] failed
		Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Mailbox export request succeeded failed for unknown reason for user: ' . $_GET['email_address']);
		$ajaxResponseVariables[AjaxConstants::RESPONSE_CONTENTS_VARIABLE]['export_status_request'] = 'failure';
	}
}
catch (InvalidHttpAuthorization $e)
{
	$arrReturnData[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_AUTHORIZATION_ERROR;
	Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for mailbox export due to HTTP Authorization Error');
}
catch (DomainCannotUseApiException $e)
{
	$arrReturnData[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_DOMAIN_CANNOT_USE_API;	
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for mailbox export due to DomainCannotUseApiException');
}
catch (UserCannotUseApiException $e)
{
	$arrReturnData[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_USER_NOT_AUTHORIZED_API;
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for mailbox export due to UserCannotUseApiException');
}
catch (NoOAuthTokenRepository $e)
{
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_NO_OAUTH_TOKEN_REPOSITORY;	
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for mailbox export due to no presence of OAuthTokenRepository for user');
}
catch (InvalidEmailaddress $e)
{
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_INVALID_USER;	
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for mailbox export due to InvalidEmailaddress for user');
}
catch (Exception $e)
{
	$arrReturnData[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_UNCAUGHT_ERROR;
	Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for mailbox export due to uncought exception');
}
echo json_encode($ajaxResponseVariables);

flush();
?>
