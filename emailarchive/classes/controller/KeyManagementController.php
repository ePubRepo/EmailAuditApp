<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class KeyManagementController
{	
	public function checkForExtantBase64PublicKey($domainname)
	{
		if (!GlobalFunctions::validateFullDomainname($domainname))
		{
			throw new InvalidDomainname();
		}
		
		$file_to_search_for_pub = GlobalConstants::getAbsolutePathToEmailArchiveRootDirectory() . GlobalConstants::getGpgKeyFoldername() . $domainname . ".pub.base64.txt";
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Check for Extant Base64 Publickey Pair for ' . $domainname . ' checking file: ' . $file_to_search_for_pub);
		if (!file_exists($file_to_search_for_pub))
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Check for Extant Base64 Publickey Pair for ' . $domainname . ' returned FALSE');
			return false;
		}
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Check for Extant Base64 Publickey Pair for ' . $domainname . ' returned TRUE');
		return true;
	}

	public function checkForBase64PublicKeyHealth($domainname)
	{
		if (!GlobalFunctions::validateFullDomainname($domainname))
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Invalid domainname: ' . $domainname . '');
			throw new InvalidDomainname();
		}
		//TODO: check key health by exporting, checking for length of string, checkign for certain words, checking for base64
		return true;
	}
	
	public function getPathToPublicKeyUploadHistoryFile()
	{
		return GlobalConstants::getAbsolutePathToEmailArchiveRootDirectory() . GlobalConstants::getGpgKeyFoldername() . 'publickeyuploadhistory.txt';
	}
	
	public function isBase64PublicKeyAlreadyUploaded($domainname)
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . '  Beginning KeyManagementController::isBase64PublicKeyAlreadyUploaded() Checking to determine whether a base64 public key has been uploaded for domain: ' . $domainname); 
		
		if (!GlobalFunctions::validateFullDomainname($domainname))
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Invalid domainname: ' . $domainname . '');	
			throw new InvalidDomainname();
		}
		
		$pathToBase64PublicKeyUploadHistoryFile = self::getPathToPublicKeyUploadHistoryFile();
		if (!file_exists($pathToBase64PublicKeyUploadHistoryFile))
		{
			//upload history file does not exist, create a blank one
			$blankFile = fopen($pathToBase64PublicKeyUploadHistoryFile, "w");
			if ($blankFile == false)
			{
				Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failure opening file: ' . $pathToBase64PublicKeyUploadHistoryFile);
				throw new IOException();
			}
			fclose($blankFile);
		}
		
		$uploaded_domains = file_get_contents($pathToBase64PublicKeyUploadHistoryFile);
		if (preg_match('/^' . $domainname . '/m', $uploaded_domains) == 1)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . '  In KeyManagementController::isBase64PublicKeyAlreadyUploaded() Check to determine whether a base64 public key has been uploaded for domain ' . $domainname . ' returned TRUE (i.e., key already uploaded)');
			return true;
		}
		else
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . '  In KeyManagementController::isBase64PublicKeyAlreadyUploaded() Check to determine whether a base64 public key has been uploaded for domain ' . $domainname . ' returned FALSE (i.e., key not already uploaded)');
			return false;
		}
	}
	
	public function generateBase64PublicKeyFile($domainname)
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . '  Beginning KeyManagementController::generateBase64PublicKeyFile() ' . $domainname);
		
		if (!GlobalFunctions::validateFullDomainname($domainname))
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Invalid domainname: ' . $domainname . '');
			throw new InvalidDomainname();
		}
		
		$expectedPublicKeyFilePath = GlobalConstants::getAbsolutePathToEmailArchiveRootDirectory() . GlobalConstants::getGpgKeyFoldername() . $domainname . '.pub';
		if (!file_exists($expectedPublicKeyFilePath))
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Expected a public key to be present for domain ' . $domainname . ' at location ' . $expectedPublicKeyFilePath . ' but could not find it');
			throw new Base64PublicKeyExportFailureException();
		}
		
		// execute bash script to write base64 public key to disk 	
		$script_to_execute = GlobalConstants::getAbsolutePathToEmailArchiveRootDirectory() . GlobalConstants::getBinDirectory() . 'write_base64_public_key_to_disk_by_domain.sh "' . $domainname . '"'; 
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Bash Script to generate base64 encoded public key file for ' . $domainname . ': ' . $script_to_execute);	
		$returned = exec($script_to_execute, $output);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Bash Script to generate base64 encoded public key file for ' . $domainname . ' returned: ' . $returned);
		
		if ($returned != 'SUCCESS')
		{
			throw new Base64PublicKeyExportFailureException();
		}
	}
	
	public function checkForExtantKeyPair($domainname)
	{
		if (!GlobalFunctions::validateFullDomainname($domainname))
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Invalid domainname: ' . $domainname . '');
			throw new InvalidDomainname();
		}
		
		$file_to_search_for_pub = GlobalConstants::getAbsolutePathToEmailArchiveRootDirectory() . GlobalConstants::getGpgKeyFoldername() . $domainname . ".pub";
		$file_to_search_for_sec = GlobalConstants::getAbsolutePathToEmailArchiveRootDirectory() . GlobalConstants::getGpgKeyFoldername() . $domainname . ".sec";
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Check for Extant Publickey Pair for ' . $domainname . '; Going to Check Path: ' . $file_to_search_for_pub);
		if (!file_exists($file_to_search_for_pub) || !file_exists($file_to_search_for_sec))
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Check for Extant Publickey Pair for ' . $domainname . ' returned FALSE; either Public Key, Private Key, or both do not exist');
			return false;
		}
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Check for Extant Publickey Pair for ' . $domainname . ' returned TRUE');
		return true;
	}
	
	public function checkForKeyPairHealth($domainname)
	{
		if (!GlobalFunctions::validateFullDomainname($domainname))
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Invalid domainname: ' . $domainname . '');
			throw new InvalidDomainname();
		}
		
		//TODO: check key health by exporting, checking for length of string, checkign for certain words, checking for base64
		return true;
	}
	
	public function generateKeyPair($domainname)
	{
		if (!GlobalFunctions::validateFullDomainname($domainname))
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Invalid domainname: ' . $domainname . '');
			throw new InvalidDomainname();
		}
	
		// check to see whether keypair already exists
		if (self::checkForExtantKeyPair($domainname))
			return true;
		
		// log time of key generation
		$start_timestamp = mktime();
			
		// generate the new keypair
		$command = GlobalConstants::getAbsolutePathToEmailArchiveRootDirectory() . GlobalConstants::getBinDirectory() . 'rsa_domain_key_generator.sh "' . $domainname . '"';
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' From KeyManagementController::generateKeyPair(), executing command: ' . $command); 
		exec($command);
		
		// check to see if key creation occured properly
		if (!self::checkForExtantKeyPair($domainname))
		{
                        Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failed Created keypair in KeyManagementController::generateKeyPair() for domain  ' . $domainname);
                        throw new KeypairCreationFailureException();

		}

		$finish_timestamp = mktime();
		
		// error if key creation timestamp takes more than a minute and a half
		if (($finish_timestamp - $start_timestamp) > 90) {
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Generation of GPG keypair for ' . $domainname . ' took too long--' . ($finish_timestamp - $start_timestamp) . ' seconds');
		}
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Generation of GPG keypair for ' . $domainname . ' took ' . ($finish_timestamp - $start_timestamp) . ' seconds');
		
		// write the base64 encoded public key to disk
		$command = GlobalConstants::getAbsolutePathToEmailArchiveRootDirectory() . GlobalConstants::getBinDirectory() . 'write_base64_public_key_to_disk_by_domain.sh "' . $domainname . '"';
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' From KeyManagementController::generateKeyPair() : ' . $command);
		exec($command);

		if (self::checkForExtantKeyPair($domainname)
			&& self::checkForKeyPairHealth($domainname))
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Successfully Created keypair in KeyManagementController::generateKeyPair() for domain  ' . $domainname);
			return true;
		}
		else
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failed Created keypair in KeyManagementController::generateKeyPair() for domain  ' . $domainname);
			throw new KeypairCreationFailureException();
		}
	}

	public function uploadPublicKey($domainnameToUpload)
	{
		if (!GlobalFunctions::validateFullDomainname($domainnameToUpload))
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Invalid domainname: ' . $domainname . '');
			throw new InvalidDomainname();
		}
	
		// check to see whether public key exists
		if (!self::checkForExtantKeyPair($domainnameToUpload))
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Failure to find Public Key: ' . $domainnameToUpload);
			throw new PublicKeyDneException();		
		}
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Successfully Found Public Key: ' . $domainnameToUpload);

		// cheeck to see whether base64 encoded version of public key exists on disk
		$file_to_search_for = GlobalConstants::getAbsolutePathToEmailArchiveRootDirectory() . GlobalConstants::getGpgKeyFoldername() . $domainnameToUpload . ".pub.base64.txt";
		if (!file_exists($file_to_search_for))
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Failure to find Base64 Encoded Public Key: ' . $file_to_search_for);
			throw new Base64PublicKeyDneException();
		}
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Successfully Found Base64 Public Key: ' . $file_to_search_for);
		
		$base64_public_key = file_get_contents($file_to_search_for);
		
		$emailAddress = AccessIdentityController::getPurportedEmailFromHttpRequest();
				
		$scope = "https://apps-apis.google.com/a/feeds/user/";
		
		$myOauthRequest = new OAuth1Request(HTTPConstants::METHOD_POST, 'https://apps-apis.google.com/a/feeds/compliance/audit/publickey/' . $domainnameToUpload, OAuth1Request::OAUTH_AUTHENTICATION_METHOD_QUERYSTRING);
		$myOauthRequest->setOauthToken(AccessIdentityController::getOauthAccessTokenFromFile($emailAddress, $scope));
		$myOauthRequest->setOauthTokenSecret(AccessIdentityController::getOauthAccessTokenSecretFromFile($emailAddress, $scope));
		$myOauthRequest->overrideOauthConsumerKey(OAuthConstants::WEB_APPLICATION_CONSUMER_KEY);
		$myOauthRequest->overrideOauthConsumerSecret(OAuthConstants::WEB_APPLICATION_CONSUMER_SECRET);
		$myOauthRequest->constructOauthRequest();
		
		$myRequest = new HTTPPostRequest();
		$myRequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
		$myRequest->setConnectionHost("apps-apis.google.com");
		$myRequest->setHttpHost("apps-apis.google.com");
		$myRequest->setPath('a/feeds/compliance/audit/publickey/' . $domainnameToUpload);
		$myRequest->setPostData('<atom:entry xmlns:atom="http://www.w3.org/2005/Atom" xmlns:apps="http://schemas.google.com/apps/2006">
		<apps:property name="publicKey" value="' . $base64_public_key . '"/>
		</atom:entry>');
		$myRequest->addHeader(new HTTPHeader('Accept', '*/*'));
		$myRequest->addHeader(new HTTPHeader('Authorization', $myOauthRequest->getFinalOauthAuthorizationHeader()));
		$myRequest->addHeader(new HTTPHeader('Content-Type', 'application/atom+xml'));
		$myRequest->addHeader(new HTTPHeader('GData-Version', '2.0'));
		$myRequest->executeRequest();
		
		if ($myRequest->getHttpResponse()->getResponseStatusCode() == 201)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' 201 Status Returned -- Successfully Uploaded Base64 Public Key: ' . $file_to_search_for);
			
			$pathToBase64PublicKeyUploadHistoryFile = self::getPathToPublicKeyUploadHistoryFile();
			if (!file_exists($pathToBase64PublicKeyUploadHistoryFile))
			{
				//upload history file does not exist, create a blank one
				$blankFile = fopen($pathToBase64PublicKeyUploadHistoryFile, "w");
				if ($blankFile == false)
				{
					Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failure opening file: ' . $pathToBase64PublicKeyUploadHistoryFile);
					throw new IOException();
				}
				fclose($blankFile);
			}
			
			//check to see if domain is already registered as having had the public key uploaded
			//if so, note this error and do not write the domain to disk (thereby preventing a doublewrite)
			if (self::isBase64PublicKeyAlreadyUploaded($domainnameToUpload))
			{
				Logger::add_error_log_entry(__FILE__ . __LINE__ . ' A public key was uploaded twice for a domain, indicating an error/inconsistency in the publickeyupload history; domain: ' . $domainnameToUpload);
				return true;
			}
			
			$fh = fopen($pathToBase64PublicKeyUploadHistoryFile, 'a') or Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Unable to open file publickey upload history file at:' . $pathToBase64PublicKeyUploadHistoryFile);
			if ($fh == false)
			{
				Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failure opening file: ' . $pathToBase64PublicKeyUploadHistoryFile);
				throw new IOException();
			}
			$stringData = "$domainnameToUpload - " . mktime() . "\n";
			fwrite($fh, $stringData);
			fclose($fh);
			
			return true;
		}
		else if ($myRequest->getHttpResponse()->getResponseStatusCode() == 401)
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failure to Upload Publickey for domain ' . $domainnameToUpload . ': ' . $myRequest->getHttpResponse()->getResponseContent());	
			throw new InvalidHttpAuthorization();
		}
		else
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failure to Upload Publickey for domain ' . $domainnameToUpload . ': ' . $myRequest->getHttpResponse()->getResponseContent());
			throw new KeyUploadFailureException();
		}
	}
}

class PublicKeyDneException extends Exception
{

}

class Base64PublicKeyExportFailureException extends Exception
{

}

class Base64PublicKeyDneException extends Exception
{

}

class KeyUploadFailureException extends Exception
{

}

class KeypairCreationFailureException extends Exception
{
	
}
?>
