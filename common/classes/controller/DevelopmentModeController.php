<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

abstract class DevelopmentModeController
{
	const DEV_MODE_ENABLED = '1';
	const DEV_MODE_DISABLED = '0'; 
	
	public function showDevelopmentMode()
	{
		if (self::getIsDevelopmentModeCookie() == self::DEV_MODE_ENABLED)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' In Development Mode');	
			return true;
		}
		return false;
	}
	
	private function getIsDevelopmentModeCookie()
	{
		if (isset($_COOKIE[GlobalConstants::getDebugCookieName()]))
		{
			return urldecode($_COOKIE[GlobalConstants::getDebugCookieName()]);
		}
		return null;
	}
	
	public function enableDevelopmentMode()
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Request to Enable Development Mode');
		$username = AccessIdentityController::getPurportedEmailFromHttpRequest();
		$domain = GlobalFunctions::getDomainFromFullEmailAddress($username);
		
		if (in_array($domain, array('apps-email.info', 'apps-apps.info', 'vocabulary-words.com')))
		{
			self::setIsDevelopmentMode(self::DEV_MODE_ENABLED);
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Request to Enable Development Mode PASSED for username ' . $username);
		}
		else
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Request to Enable Development Mode FAILED for username ' . $username);
		}
	}
	
	public function revokeDevelopmentMode()
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Revoking debug cookie');
		setcookie(GlobalConstants::getDebugCookieName(), '', 1, '/', 'www.apps-apps.info', true, true);
	}
	
	private function setIsDevelopmentMode($development_mode)
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Set Development Mode Cookie Value of: ' . $development_mode);
		setcookie(GlobalConstants::getDebugCookieName(), $development_mode, GlobalConstants::getCookieDurationValue(), '/', 'www.apps-apps.info', true, true);
	}
}
?>
