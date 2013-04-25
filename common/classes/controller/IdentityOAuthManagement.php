<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

abstract class OAuthTokenConstants
{
	const OAUTH_VERSION = 'oauth_version';
	const OAUTH_ACCESS_TOKEN = 'oauth_access_token';
	const OAUTH_ACCESS_TOKEN_SECRET = 'oauth_access_token_secret';
	const OAUTH_ACCESS_TOKEN_SCOPE = 'oauth_access_token_scope';
	const OAUTH_ACCESS_TOKEN_TIMESTAMP = 'oauth_access_token_timestamp';
}

class OAuthToken
{
	private $oauth_version = "1.0";
	private $oauth_access_token;
	private $oauth_access_token_secret;
	private $oauth_access_token_scope;
	private $oauth_access_token_timestamp;
	
	public function getOAuthTokenVariable($variable_name)
	{
		return $this->{$variable_name};
	}
	
	public function setOAuthTokenVariable($variable_name, $variable_value)
	{
		$this->{$variable_name} = $variable_value;
	}
}

class OAuthTokenRepository
{
	private $arrOauthTokens = array();
	
	public function addOAuthToken(OAuthToken $token)
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' OauthTokenRepository Currently Has ' . count($this->arrOauthTokens) . ' tokens');
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Attempt to add new OAuth token with scope: ' . $token->getOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SCOPE));

		/**
		 * Check to see if an existing OAuthToken exists for the scope
		 * for which the new token is authorized (i.e., use scope to check for a conflict of tokens)
		 *
		 * If a conflict exists, remove the old token so it is safe to add the new token
		 */
		if (strpos(serialize($this->arrOauthTokens), $token->getOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SCOPE)) > 0)
		{
			for ($i = 0; $i < count($this->arrOauthTokens); $i++)
			{	
				if ($this->arrOauthTokens[$i]->getOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SCOPE) == $token->getOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SCOPE))
				{
					Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Found duplicate scope OAuthToken (will need to remove old token) for scope: ' . 	$this->arrOauthTokens[$i]->getOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SCOPE));
					$extracted = array_splice($this->arrOauthTokens, $i, 1);
				}
			}
		}
		
		if (substr_count(serialize($this->arrOauthTokens), $token->getOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SCOPE)) > 0)
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Multiple OAuth Tokens for Same Scope: ' . $token->getOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SCOPE));
		}
		
		array_push($this->arrOauthTokens, $token);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Added new OAuth token with scope: ' . $token->getOAuthTokenVariable(OAuthTokenConstants::OAUTH_ACCESS_TOKEN_SCOPE));
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' OAuthToken Repository now has ' . count($this->arrOauthTokens) . ' tokens');
	}
	
	public function getOAuthTokens()
	{
		return $this->arrOauthTokens;
	}
}

abstract class IdentityConstants
{
	const FIRST_NAME = 'first_name';
	const LAST_NAME = 'last_name';
	const EMAIL_ADDRESS = 'email_address';
	const CLAIMED_ID = 'claimed_id';
	const MARKETPLACE_INSTALLED = 'marketplace_installed';
	
	const IDENTITY_SECRET = 'identity_secret';
	const IDENTITY_SECRET_TIMESTAMP = 'identity_secret_timestamp';
	const LAST_IDENTITY_VALIDATED_TIMESTAMP = 'last_validated_timestamp';
}

class Identity
{
	private $first_name;
	private $last_name;
	private $email_address;
	private $claimed_id;
	private $marketplace_installed;
	
	private $identity_secret;
	private $identity_secret_timestamp;
	private $first_identity_validated_timestamp;
	private $last_identity_validated_timestamp;
	
	public function getIdentityVariable($variable_name)
	{
		return $this->{$variable_name};
	}
	
	public function setIdentityVariable($variable_name, $variable_value)
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Within Identity::setIdentityVariable() setting ' . $variable_name . ' set to value ' . $variable_value);
		$this->{$variable_name} = $variable_value;
	}
}

/*
 * This is the class that should be serialized and stored to disk
 */
class IdentityRepositoryEntry
{
	private $identity;
	private $oauth_token_respository;
	
	public function setIdentity(Identity $identity)
	{
		$this->identity = $identity;
	}
	
	public function getIdentity()
	{
		return $this->identity;
	}
	
	public function setOAuthTokenRepository(OAuthTokenRepository $repository)
	{
		$this->oauth_token_respository = $repository;
	}
	
	public function getOAuthTokenRepository()
	{
		return $this->oauth_token_respository;
	}
}

class IdentityRepositoryHelper
{
	private $input_identity;
	private $input_oauth_token_repository;
	
	private $merged_identity_repository_entry;
	
	public function getAbsolutePathToIdentityDirectory()
	{
		return GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/' . GlobalConstants::getIdentityManagementFoldername();
	}
	
	public function setIdentity(Identity $identity)
	{
		$this->input_identity = $identity;
	}

	public function setOAuthTokenRepository(OAuthTokenRepository $repository)
	{
		$this->input_oauth_token_repository = $repository;
	}
	
	public function doesIdentityRepositoryExist($email)
	{
		if (!GlobalFunctions::validateFullEmailAddress($email))
			throw new InvalidEmailaddress();
			
		$exists = file_exists(self::getAbsolutePathToIdentityDirectory() . $email);
		Logger::add_info_log_entry(__FILE__ . __LINE__ .  ' Check whether IdentityRepository exists for ' . $email . ' returned ' . (($exists === true) ? 'true' : 'false') . '; Checked location: ' . self::getAbsolutePathToIdentityDirectory() . $email);
		return $exists;
	}

	/*
	 * @return IdentityRepositoryEntry
	 */
	public function getIdentityRepositoryEntryFromDisk($email)
	{
		if (!GlobalFunctions::validateFullEmailAddress($email))
			throw new InvalidEmailaddress();
		
		if (!self::doesIdentityRepositoryExist($email))
		{
			throw new IOException();
		}
		
		$contents = file_get_contents(self::getAbsolutePathToIdentityDirectory() . $email);
		$unserialize_contents = unserialize($contents);
		if ($unserialize_contents instanceof IdentityRepositoryEntry)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ .  ' Dump of Identity Repository for User ' . $email . ' ----- ' . $contents);
			return $unserialize_contents;
		}
		else
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ .  ' Corruption when attempting to unserialize contents ' . $email);
			throw new IOException();
		}
	}
	
	public function storeIdentityRepositoryToDisk()
	{
		// STEP 0: Check for Pre-requisite Data
		// Identity is required in order to associate an email address
		// OAuthTokens are not required
		// Check for valid email, whose absence will cause problems
		if (!($this->input_identity instanceof Identity)
			&& preg_match("/^[^@]*@[^@]*\.[^@]*$/", $this->input_identity->getIdentityVariable(IdentityConstants::EMAIL_ADDRESS)) == 1)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Invalid Pre-Requisite Data for Executing IdentityRepositoryHelper::storeIdentityRepositoryToDisk(); Email Address: ' . $this->input_identity->getIdentityVariable(IdentityConstants::EMAIL_ADDRESS));
			throw new InvalidIdentityRepositoryHelperArguments();
		}
		
		// STEP 1: Create a Merged Repository
		$this->createMergedIdentityRepositoryEntry();
		
		// STEP 2: Serialize data
		// check to ensure valid Identity data; absence of valid identity data will cause major problems
		if (!($this->merged_identity_repository_entry->getIdentity() instanceof Identity)
			|| preg_match("/^[^@]*@[^@]*\.[^@]*$/", $this->merged_identity_repository_entry->getIdentity()->getIdentityVariable(IdentityConstants::EMAIL_ADDRESS)) != 1
			|| strlen($this->merged_identity_repository_entry->getIdentity()->getIdentityVariable(IdentityConstants::IDENTITY_SECRET)) < 5)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Invalid Data for Writing to Disk in IdentityRepositoryHelper::storeIdentityRepositoryToDisk(); Email Address: ' . $this->merged_identity_repository_entry->getIdentity()->getIdentityVariable(IdentityConstants::EMAIL_ADDRESS));
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Invalid Data for Writing to Disk in IdentityRepositoryHelper::storeIdentityRepositoryToDisk(); Identity Secret: ' . $this->merged_identity_repository_entry->getIdentity()->getIdentityVariable(IdentityConstants::IDENTITY_SECRET));
			throw new InvalidIdentityRepositoryHelperArguments();
		}
		
		$serialize_repository = serialize($this->merged_identity_repository_entry);
		
		// STEP 3: Write data to disk
		$fh = fopen(self::getAbsolutePathToIdentityDirectory() . $this->merged_identity_repository_entry->getIdentity()->getIdentityVariable(IdentityConstants::EMAIL_ADDRESS), 'w');
		if ($fh === false)
		{
			throw new IOException();
		}
		
		$success = fwrite($fh, $serialize_repository);
		if ($success === false)
		{
			throw new IOException();
		}
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Successfully stored IdentityRepositoryEntry ' . $email_addr);
	}
	
	/*
	 * The arguments passed into this helper class, Identity and OAuthTokenRepository, may reflect only part of a person's identity.
	 * 
	 * This helper class checks for an IdentityRepositoryEntry onfile and merges the current one and the on-disk one
	 */
	private function createMergedIdentityRepositoryEntry()
	{
		//STEP 0: Check for Pre-requisite Data
		if (!($this->input_identity instanceof Identity)
			&& preg_match("/^[^@]*@[^@]*\.[^@]*$/", $this->input_identity->getIdentityVariable(IdentityConstants::EMAIL_ADDRESS)) == 1)
		{
			throw new InvalidIdentityRepositoryHelperArguments();
		}
		
		//STEP 1: Check for Extant Identity Repository
		$identityRepositoryExistsOnDisk = self::doesIdentityRepositoryExist($this->input_identity->getIdentityVariable(IdentityConstants::EMAIL_ADDRESS));
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Check for Extant Identity Repository by email ' . $this->input_identity->getIdentityVariable(IdentityConstants::EMAIL_ADDRESS) . ' returned: ' . (($identityRepositoryExistsOnDisk === true) ? 'true' : 'false'));
		
		//STEP 2: Merge Identity Repository Variables
		//STEP 2.A: MERGE IDENTITY
		if ($identityRepositoryExistsOnDisk)
		{
			$myMergedIdentity = new Identity();
						
			$myOldIdentity = self::getIdentityRepositoryEntryFromDisk($this->input_identity->getIdentityVariable(IdentityConstants::EMAIL_ADDRESS))->getIdentity();
			if (! ($myOldIdentity instanceof Identity))
			{
				throw new InvalidIdentityRepositoryHelperArguments();
			}
		
			// new identity secret should simply be old identity secret; identity secret should never change, same with email
			$oldIdentityMarketplaceInstalled = $myOldIdentity->getIdentityVariable(IdentityConstants::MARKETPLACE_INSTALLED);
			$oldIdentitySecret = $myOldIdentity->getIdentityVariable(IdentityConstants::IDENTITY_SECRET);
			$oldEmailAddress = $myOldIdentity->getIdentityVariable(IdentityConstants::EMAIL_ADDRESS);
			$oldIdentitySecretTimestamp = $myOldIdentity->getIdentityVariable(IdentityConstants::IDENTITY_SECRET_TIMESTAMP);
			$oldClaimedId = $myOldIdentity->getIdentityVariable(IdentityConstants::CLAIMED_ID);
			$myMergedIdentity->setIdentityVariable(IdentityConstants::IDENTITY_SECRET, $oldIdentitySecret);
			$myMergedIdentity->setIdentityVariable(IdentityConstants::EMAIL_ADDRESS, $oldEmailAddress); 
			$myMergedIdentity->setIdentityVariable(IdentityConstants::IDENTITY_SECRET_TIMESTAMP, $oldIdentitySecretTimestamp);
			$myMergedIdentity->setIdentityVariable(IdentityConstants::CLAIMED_ID, $oldClaimedId);
			$myMergedIdentity->setIdentityVariable(IdentityConstants::MARKETPLACE_INSTALLED, $oldIdentityMarketplaceInstalled);
			
			// new first name and new last name should be what is returned by the new Identity if it exists; otherwise, use what is present
			$oldFirstName = $myOldIdentity->getIdentityVariable(IdentityConstants::FIRST_NAME);
			$oldLastName = $myOldIdentity->getIdentityVariable(IdentityConstants::LAST_NAME);
			$mergedFirstName = (strlen($this->input_identity->getIdentityVariable(IdentityConstants::FIRST_NAME) >= 1)) ? $this->input_identity->getIdentityVariable(IdentityConstants::FIRST_NAME) : $oldFirstName; 
			$mergedLastName = (strlen($this->input_identity->getIdentityVariable(IdentityConstants::LAST_NAME) >= 1)) ? $this->input_identity->getIdentityVariable(IdentityConstants::LAST_NAME) : $oldFirstName;			
			$myMergedIdentity->setIdentityVariable(IdentityConstants::FIRST_NAME, $mergedFirstName);
			$myMergedIdentity->setIdentityVariable(IdentityConstants::LAST_NAME, $mergedLastName);
			
			// incase identity secret and email exist on disk, but not yet in the user's browser, set the cookies
			AccessIdentityController::setIdentitySecretCookie($oldIdentitySecret);
			AccessIdentityController::setEmailCooke($oldEmailAddress);
		}
		else
		{
			$myMergedIdentity = $this->input_identity;
			
			//no previous identity exists; need to assign an identity secret to the Identity
			$newIdentitySecret = AccessIdentityController::getNewIdentitySecret();
			$myMergedIdentity->setIdentityVariable(IdentityConstants::IDENTITY_SECRET, $newIdentitySecret);
			$myMergedIdentity->setIdentityVariable(IdentityConstants::IDENTITY_SECRET_TIMESTAMP, mktime());
			AccessIdentityController::setIdentitySecretCookie($newIdentitySecret);
			AccessIdentityController::setEmailCooke($this->input_identity->getIdentityVariable(IdentityConstants::EMAIL_ADDRESS));
		}
		
		$myMergedIdentity->setIdentityVariable(IdentityConstants::LAST_IDENTITY_VALIDATED_TIMESTAMP, mktime());
		
		//STEP 2.B: MERGE OAUTH REPOSITORy
		$mergedOAuthTokenRepository = new OAuthTokenRepository();
		$myOldOAuthTokenRepository = ($identityRepositoryExistsOnDisk) ? self::getIdentityRepositoryEntryFromDisk($this->input_identity->getIdentityVariable(IdentityConstants::EMAIL_ADDRESS))->getOAuthTokenRepository() : null;
		
		/*
		 * Use Case 1: No Old Repository, New Repository
		 * 		Set $mergedOAuthTokenRepository = New Repository
		 * 
		 * Use Case 2: Old Repository, No New Repository
		 * 		Set $mergedOAuthTokenRepository = Old Repository
		 * 
		 * Use Case 3: Old Repository, New Repository
		 * 		Set $mergedOAuthTokenRepository = Old Repository
		 * 		Then, iterate over New Repository and add each OAuthToken to $mergedOAuthTokenRepository
		 * 			Since adding OAuthTokens to a repository is write-safe, it will handle the overwriting 
		 * 
		 * Use Case 4: No Old Repository, No New Repository
		 * 		Set $mergedOAuthTokenRepository = null
		 */
		
		if (!($myOldOAuthTokenRepository instanceof OAuthTokenRepository)
			&& ($this->input_oauth_token_repository instanceof OAuthTokenRepository)
			&& count($this->input_oauth_token_repository->getOAuthTokens()) > 0)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Merging OAuth Token Repository Use Case #1 -- No Old Repository, New Repository');
			$mergedOAuthTokenRepository = $this->input_oauth_token_repository; 
		}
		else if (($myOldOAuthTokenRepository instanceof OAuthTokenRepository)
			&& count($myOldOAuthTokenRepository->getOAuthTokens()) > 0
			&& !($this->input_oauth_token_repository instanceof OAuthTokenRepository))
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Merging OAuth Token Repository Use Case #2 -- Old Repository, No New Repository');
			$mergedOAuthTokenRepository = $myOldOAuthTokenRepository;
		}
		else if (($myOldOAuthTokenRepository instanceof OAuthTokenRepository)
			&& count($myOldOAuthTokenRepository->getOAuthTokens()) > 0
			&& ($this->input_oauth_token_repository instanceof OAuthTokenRepository)
			&& count($this->input_oauth_token_repository->getOAuthTokens()) > 0)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Merging OAuth Token Repository Use Case #3 -- Old Repository, New Repository; Need to Merge');
			$mergedOAuthTokenRepository = $myOldOAuthTokenRepository;
			foreach ($this->input_oauth_token_repository->getOAuthTokens() as $newOAuthToken)
			{
				if (!($newOAuthToken instanceof OAuthToken))
					continue;
				$mergedOAuthTokenRepository->addOAuthToken($newOAuthToken);
			}
		}
		else if (!($myOldOAuthTokenRepository instanceof OAuthTokenRepository)
			&& !($this->input_oauth_token_repository instanceof OAuthTokenRepository))
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Merging OAuth Token Repository Use Case #4 -- No Old Repository, No New Repository');
			$mergedOAuthTokenRepository = null;
		}
				
		//STEP 3: Assemble New IdentityRepositoryEntry
		$myRepository = new IdentityRepositoryEntry();
		$myRepository->setIdentity($myMergedIdentity);
		
		/*
		 * add OAuthTokenRepository to the IdentityRepositoryEntry
		 * it is required to add an OAuthTokenRepository, even if it is blank
		 * the absence of an OAuthTokenRepository will cause a number of problems down the road
		 */
		if ($mergedOAuthTokenRepository instanceof OAuthTokenRepository)
		{
			$myRepository->setOAuthTokenRepository($mergedOAuthTokenRepository);
		}
		
		//STEP 4: Set Merged Repository
		$this->merged_identity_repository_entry = $myRepository;
	}
}

class InvalidIdentityRepositoryHelperArguments extends Exception
{

}

?>
