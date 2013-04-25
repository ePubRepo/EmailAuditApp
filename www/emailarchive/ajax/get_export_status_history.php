<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('../../../emailarchive/inc_email_archive_bootstrap.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_http_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/classes/model/inc_oauth_classes.php');
require_once(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'emailarchive/classes/controller/MailboxExportStatusRequestController.php');
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

$ajaxResponseVariables = array();

try {
	$myStatusController = new MailboxExportStatusRequestController();
	$myMailboxExportStatusList = $myStatusController->retrieveMailboxExportStatusList();
	
	
	//TODO: Massive error checking
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_SUCCESSFULLY;
	$ajaxResponseVariables[AjaxConstants::RESPONSE_CONTENTS_VARIABLE]['export_requests'] = array();
	
	if (count($myMailboxExportStatusList->getArrayOfMailboxExportStatuses()) > 0)
	{
		foreach($myMailboxExportStatusList->getArrayOfMailboxExportStatuses() as $exportRequest)
		{
			array_push($ajaxResponseVariables[AjaxConstants::RESPONSE_CONTENTS_VARIABLE]['export_requests'], array(
				'status' => $exportRequest->getStatus(),
				'request_date' => str_replace(array(' ', ':'), array('-', '-'), $exportRequest->getRequestDate()),
				'request_id' => $exportRequest->getRequestId(),
				'file_url' => $exportRequest->getFileUrl(),
				'email_address' => $exportRequest->getUserEmailAddress(),
				'completed_date' => $exportRequest->getCompletedDate(),
				'request_administrator_date' => $exportRequest->getAdminEmailAddress(),
				'expired_date' => $exportRequest->getExpiredDate(),
				)
			);
		}
	}
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Successfully processed AJAX request for status of all exports, returning ' . count($myMailboxExportStatusList->getArrayOfMailboxExportStatuses()));
}
catch (InvalidHttpAuthorization $e)
{
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_AUTHORIZATION_ERROR;
	Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for export history due to HTTP Authorization Error');
}
catch (MailboxExportStatusRequestExceptoin $e)
{
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_UNCAUGHT_ERROR;
	Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for export history due to MailboxExportStatusRequestExceptoin');
}
catch (DomainCannotUseApiException $e)
{
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_DOMAIN_CANNOT_USE_API;	
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for export history due to DomainCannotUseApiException');
}
catch (UserCannotUseApiException $e)
{
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_USER_NOT_AUTHORIZED_API;	
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for export history due to UserCannotUseApiException');
}
catch (NoOAuthTokenRepository $e)
{
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_NO_OAUTH_TOKEN_REPOSITORY;	
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for export history due to no presence of OAuthTokenRepository for user');
}
catch (Exception $e)
{
	$ajaxResponseVariables[AjaxConstants::RESPONSE_PROCESS_STATUS_VARIABLE] = AjaxConstants::RESPONSE_PROCESSED_UNCAUGHT_ERROR;
	Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failed processed AJAX request for export history due to uncought exception');
}

echo json_encode($ajaxResponseVariables);

flush();
?>
