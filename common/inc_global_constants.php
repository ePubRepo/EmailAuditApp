<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

abstract class Printer
{
	public static function appendNewLineCharacters($html)
	{
		return $html . "\r\n";
	}
}

abstract class GlobalConstants
{
	const APP_EMAIL_ARCHIVE_PREFIX = "ear";
	
	public static function getCookieDurationValue()
	{
		//set cookie to last for a month
		return (time() + 60*60*24*30);
	}
	
	public static function getAbsolutePathToAppAppsRootDirectory()
	{
		return '/home/appsappsinfo/';
	}
	
	public static function getAbsolutePathToEmailArchiveRootDirectory()
	{
		return self::getAbsolutePathToAppAppsRootDirectory() . 'emailarchive/';	
	}
	
	public static function getLogsFoldername()
	{
		return 'logs/';
	}
	
	public static function getGpgKeyFoldername()
	{
		return 'datastore/gpg-keys/';
	}

	public static function getMailboxFoldername()
	{
		return 'datastore/mailboxes/';
	}
	
	public static function getBinDirectory()
	{
		return 'bin/';
	}
	
	public static function getIdentityManagementFoldername()
	{
		return 'datastore/identity_management/';
	}
	
	public static function isDetailedInfoLoggingEnabled()
	{
		return true;
	}
	
	public static function getIdentitySecretCookieName()
	{
		return 'app_identity_secret';
	}
	
	public static function getEmailAddressCookieName()
	{
		return 'app_email_address';
	}
	
	public static function getDebugCookieName()
	{
		return 'app_debug';
	}
}

abstract class GlobalFunctions
{
	public static function validateFullIdentitySecret($identitySecret)
	{
		if (strlen($identitySecret) < 30
			|| strlen($identitySecret) > 40
			|| strpos($identitySecret, ' ') !== false)
		{
			return false;
		}
		return true;
	}
	
	public static function validateFullEmailAddress($email)
	{
		preg_match_all('~([^@]+)@([^@]+)~', $email, $matches, PREG_SET_ORDER);

		if (!isset($matches[0][1]) || !isset($matches[0][2]))
		{
			return false;
		}
		return true;
	}
	
	public static function validateFullDomainname($domain)
	{
		preg_match_all('~([^@]+)\.([^@]+)~', $domain, $matches, PREG_SET_ORDER);

		if (!isset($matches[0][1]) || !isset($matches[0][2]))
		{
			return false;
		}
		return true;	
	}
	
	public static function getUsernameFromFullEmailAddress($email_address)
	{
		preg_match_all('~([^@]+)@([^@]+)~', urldecode($email_address), $matches, PREG_SET_ORDER);
		
		if (!isset($matches[0][1]) || !isset($matches[0][2]))
		{
			return false;
		}
		
		return $matches[0][1];
	}

	public static function getDomainFromFullEmailAddress($email_address)
	{
		preg_match_all('~([^@]+)@([^@]+)~', urldecode($email_address), $matches, PREG_SET_ORDER);
		
		if (!isset($matches[0][1]) || !isset($matches[0][2]))
		{
			return false;
		}
		
		return $matches[0][2];	
	} 
}

?>
