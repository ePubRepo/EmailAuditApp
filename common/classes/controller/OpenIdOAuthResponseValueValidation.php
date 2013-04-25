<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

/**
 * The purpose of this class is to validate the HTTP GET response variables sent back from Google
 * 	to the app as part of the OAuth/OpenId process.
 */
abstract class OpenIdOAuthResponseValueValidation {

	public static function validateEmailAddress($inputEmailAddress)
	{
		return GlobalFunctions::validateFullEmailAddress($inputEmailAddress);
	}

	public static function validateRequestToken($inputToken)
	{
		if (preg_match('/^[A-Za-z_\-\/0-9\.]+$/', $inputToken) != 1
			|| strlen($inputToken) > 65
			|| strlen($inputToken) < 24)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Invalid request token; Received: ' . $inputToken);
			return false;
		}
		return true;
	}
	
	public static function validateFirstOrLastName($inputName)
	{
		if (preg_match('/^[a-zA-Z ]+$/', $inputName) == 1)
		{	
			return true;
		}
		else
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Invalid first or last name; Received: ' . $inputName);
			return false;
		}
	}
	
	public static function validateOAuthScope($inputScope)
	{
		if (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $inputScope) === 1)
		{
			return true;
		}
		else
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Invalid OAuth scope; Received: ' . $inputScope);
			return false;
		}
	}

	public static function validateClaimedId($inputId)
	{
		if (preg_match('|^https://www.google.com/accounts/o8/id\?id=[A-Za-z_\*\-\/0-9]{10,80}$|i', $inputId) === 1)
		{
			return true;
		}
		else
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Invalid OpenId ClaimedId; Received: ' . $inputId);
			return false;
		}
	}
}

?>
