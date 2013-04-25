<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Top of Class EmailArchiveAccessIdentityCheckController');

abstract class EmailArchiveAccessIdentityCheckController
{
	/**
	 * @param {int} $daysIdentityValid The number of days ago an account can have successfully validated its identity
	 * before requiring new verification of identity
	 *
	 * @param {boolean} $ajaxRequest Whether the HTTP request associated with this checking is an AJAX request,
	 * in which case we will not perform HTTP Header redirection but will return a boolean and let the calling context handle the situation
	 * 
	 * @return {boolean} if $ajaxRequest = true, then function will 
	 */
	public function checkAccessIdentityAndPerformRedirection($ajaxRequest = false, $daysIdentityValid = 0.5)
	{
		/*
		 * USE CASE 1: No Identity Cookies
		 * Perform full OAuth + OpenID Authorization flow
		 * 
		 * USE CASE 2: Identity Cookies + Last Validated Timestamp > 12 hours
		 * Check to see if OAuth credentials onfile for the user are valid.
		 * If yes, perform identity check.
		 * If no, perform full OAuth + OpenID Authorization flow 
		 * 
		 * USE CASE 3: Identity Cookies + Last Validated Timestamp < 12 hours
		 * Check to see if OAuth credentials onfile for the user are valid.
		 * If yes, allow the user to proceed.
		 * If no, perform full OAuth + OpenID Authorization flow.
		 */
	
		$user_validated = AccessIdentityController::isIdentityKnownAndValidated();
		$purported_identity_secret = AccessIdentityController::getPurportedItentitySecretFromHttpRequest();
		$purported_email = AccessIdentityController::getPurportedEmailFromHttpRequest();
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' User Validated value of ' . (($user_validated === true) ? 'true' : 'false') . ' and purported email value of ' . $purported_email);
		
		if (!$user_validated
			|| strlen($purported_identity_secret) < 1
			|| strlen($purported_email) < 1)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' User has no purported email or purported identity secret or has wrong credentials, erasing all cookies and sending them to welcome page');
			AccessIdentityController::revokeIdentitySecretCookie();
			AccessIdentityController::revokeEmailCookie();
			if ($ajaxRequest == true)
			{
				return false;
			}
			header('Location: /emailarchive/welcome.php');
		}
		
		$last_validated_timestamp = AccessIdentityController::getLastValidatedTimestampByEmail($purported_email);
		
		if ($user_validated && ($last_validated_timestamp > (mktime() - 60*60*(24*$daysIdentityValid))))
		{
			//User All Set; LVT is greater than (i.e., more recent than) 12 hours ago
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' User has valid credentials and has validated in the past 12 hours');
			if ($ajaxRequest == true)
			{
				return true;
			}
		}
		else if ($user_validated && $last_validated_timestamp < (mktime() - 60*60*(24*$daysIdentityValid)))
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' User has valid credentials, but has not validated in the past 12 hours, so sending them through the OpenID flow');
			if ($ajaxRequest == true)
			{
				return false;
			}
			header('Location: /identity_check_start.php?final_landing_url=' . urlencode('/emailarchive/'));
		}
	}
}
?>
