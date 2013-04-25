<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

abstract class AccessIdentityController
{
	public function getNewIdentitySecret()
	{
		return md5(time() . rand(100,999) . rand(100,999)) . rand(100,999);
	}
	
	public function setIdentitySecretCookie($identity_secret)
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Set Identity Secret Cookie Value of: ' . $identity_secret);
		setcookie(GlobalConstants::getIdentitySecretCookieName(), $identity_secret, GlobalConstants::getCookieDurationValue(), '/', 'www.apps-apps.info', true, true);
	}
	
	public function revokeIdentitySecretCookie()
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Revoking identity secret cookie, which currently has a value of: ' . self::getPurportedItentitySecretFromHttpRequest());
		setcookie(GlobalConstants::getIdentitySecretCookieName(), '', 1, '/', 'www.apps-apps.info', true, true);
	}
	
	public function getPurportedItentitySecretFromHttpRequest()
	{
		if (isset($_COOKIE[GlobalConstants::getIdentitySecretCookieName()]))
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Getting identity secret cookie, which currently has a value of: ' . $_COOKIE[GlobalConstants::getIdentitySecretCookieName()]);
			return urldecode($_COOKIE[GlobalConstants::getIdentitySecretCookieName()]);
		}
		return null;
	}
	
	public function getPurportedEmailFromHttpRequest()
	{
		if (isset($_COOKIE[GlobalConstants::getEmailAddressCookieName()]))
		{
			return urldecode($_COOKIE[GlobalConstants::getEmailAddressCookieName()]);
		}
		return null;
	}
	
	public function setEmailCooke($email_address)
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Set Email Address Cookie Value of: ' . $email_address);
		setcookie(GlobalConstants::getEmailAddressCookieName(), $email_address, GlobalConstants::getCookieDurationValue(), '/', 'www.apps-apps.info', true, true);
	}
	
	public function revokeEmailCookie()
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Revoking email cookie, which currently has a value of: ' . self::getPurportedEmailFromHttpRequest());
		setcookie(GlobalConstants::getEmailAddressCookieName(), '', 1, '/', 'www.apps-apps.info', true, true);
	}
	
	public function isIdentityKnownAndValidated()
	{
		$purported_identity_secret = self::getPurportedItentitySecretFromHttpRequest();
		$purported_email = self::getPurportedEmailFromHttpRequest();

		// check to ensure purported email and identity secret are valid possible values
		// this prevents cookie-injection attacks
		if (strlen($purported_email) > 1 && strlen($purported_identity_secret) > 1 
 			&& (!GlobalFunctions::validateFullEmailAddress($purported_email)
			|| !GlobalFunctions::validateFullIdentitySecret($purported_identity_secret))
		  )
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Email address or Identity secret sent in headers are not possibly valuable');
			return false;		
		}
		
		try
		{
			$valid_identity_secret = self::getValidIdentitySecretByEmail($purported_email);
		}
		catch (InvalidEmailaddress $e)
		{
			return false;
		}
		catch (NoSuchUserException $e)
		{
			return false;
		}
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " Identity Check, Purported Email: " . $purported_email . " // Purported Identity Secret: " . $purported_identity_secret .  " // Actual Identity Secret: " . $valid_identity_secret);
		
		if ($valid_identity_secret === $purported_identity_secret)
		{
			return true;
		}
		return false;
	}
	
	/*
	 * @return url-unencoded OAuth access token
	 */
	public function getOauthAccessTokenFromFile($email, $scope)
	{
		$exists = file_exists(GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/' . GlobalConstants::getIdentityManagementFoldername() . $email);
		if ($exists === false)
		{
			throw new NoSuchUserException();
		}

		$myIdentityRepositoryEntry = IdentityRepositoryHelper::getIdentityRepositoryEntryFromDisk($email);
		
		if ($myIdentityRepositoryEntry->getOAuthTokenRepository() instanceof OAuthTokenRepository)
		{
			$arrTokens = $myIdentityRepositoryEntry->getOAuthTokenRepository()->getOAuthTokens();
		}
		else
		{
			//oauthTokenRepoisotory does not have OAuthTokens -> throw exceptions
			Logger::add_warning_log_entry(__FILE__ . __LINE__ . ' Attempted to get OAuth tokens from OAuthTokenRepository when no OAuthTokenRepository existed; tokens requested for user ' . $email . ' and for scope ' . $scope);
			throw new NoOAuthTokenRepository();
		}

		foreach ($arrTokens as $token)
		{
			if ($token->getOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SCOPE) == $scope)
			{
				return $token->getOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN);
			}
		}
	}

	/*
	 * @return urlunencoded OAuth access token secret
	 */
	public function getOauthAccessTokenSecretFromFile($email, $scope)
	{
		$exists = IdentityRepositoryHelper::doesIdentityRepositoryExist($email);
		if (!$exists)
		{
			throw NoSuchUserException();
		}

		$myIdentityRepositoryEntry = IdentityRepositoryHelper::getIdentityRepositoryEntryFromDisk($email);
		$arrTokens = $myIdentityRepositoryEntry->getOAuthTokenRepository()->getOAuthTokens();
		
		foreach ($arrTokens as $token)
		{
			if ($token->getOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SCOPE) == $scope)
			{
				Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Requested Email = ' . $email . ' and Requested Scope = ' . $scope . ' returning OAuthTokenSecret:' . $token->getOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SECRET));
				return $token->getOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SECRET);
			}
		}
	}

	/*
	 * @return openid claimed id
	 */
	public function getOpenIdClaimedIdFromFile($email)
	{
		$exists = IdentityRepositoryHelper::doesIdentityRepositoryExist($email);
		if (!$exists)
		{
			throw NoSuchUserException();
		}

		$myIdentityRepositoryEntry = IdentityRepositoryHelper::getIdentityRepositoryEntryFromDisk($email);
		$claimedId = $myIdentityRepositoryEntry->getIdentity()->getIdentityVariable(IdentityConstants::CLAIMED_ID);
		
		return $claimedId;
	}
	
	public function getLastValidatedTimestampByEmail($email)
	{
		$exists = IdentityRepositoryHelper::doesIdentityRepositoryExist($email);
		if (!$exists)
		{
			throw NoSuchUserException();
		}
		
		$myIdentityRepositoryEntry = IdentityRepositoryHelper::getIdentityRepositoryEntryFromDisk($email);
		
		$lastValidatedTimestamp = $myIdentityRepositoryEntry->getIdentity()->getIdentityVariable(IdentityConstants::LAST_IDENTITY_VALIDATED_TIMESTAMP);
		if (strlen($lastValidatedTimestamp) < 4
			|| !is_int($lastValidatedTimestamp))
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . " Fetched short/invalid/corrupted Last Validated Timestamp for " . $email . " :: " . $lastValidatedTimestamp);
		}
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " Fetched Last Validated Timestamp for " . $email . " :: " . $lastValidatedTimestamp);
		return $lastValidatedTimestamp;
	}
	
	public function getValidIdentitySecretByEmail($email)
	{
		$exists = IdentityRepositoryHelper::doesIdentityRepositoryExist($email);
		if (!$exists)
		{
			throw new NoSuchUserException();
		}
		
		$myIdentityRepositoryEntry = IdentityRepositoryHelper::getIdentityRepositoryEntryFromDisk($email);
		
		$identitySecret = $myIdentityRepositoryEntry->getIdentity()->getIdentityVariable(IdentityConstants::IDENTITY_SECRET);
		if (strlen($identitySecret) < 4)
		{
			//TODO: Add much more ERROR logging where appropriate and INFO logging into other places
			Logger::add_error_log_entry(__FILE__ . __LINE__ . " Fetched short/invalid/corrupted Identity Secret for " . $email . " :: " . $identitySecret);
		}
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " Fetched Valid Identity Secret for " . $email . " :: " . $identitySecret);
		return $identitySecret;
	}
}

class NoSuchUserException extends Exception
{

}
?>
