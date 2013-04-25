<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class CreateMailboxExportRequestController
{
	const PENDING_REQUEST_STATUS_NONE = 'none';
	const PENDING_REQUEST_STATUS_PENDING = 'pending';	

	private $requestEmail;
	private $requestUsername;
	private $requestDomain;

	const REQUEST_STATUS_SUCCESS = 'success';
	const REQUEST_STATUS_FAILURE = 'failure';
	const REQUEST_STATUS_DUPLICATE = 'duplicate';
	private $requestStatus;
	
	function __construct($email)
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Class Initialized for CreateMailboxExportRequestController for Email: ' . $email);
		
		$this->requestEmail = $email;
		$this->checkInputs();
		$this->parseRequestEmail();
		
		$pending = $this->checkPendingRequests();
		if ($pending == self::PENDING_REQUEST_STATUS_PENDING)
		{
			$this->requestStatus = self::REQUEST_STATUS_DUPLICATE;
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Status of Mailbox Export Request for ' . $this->requestEmail . ' set as: ' . $this->requestStatus);
			return false;
		}
		
		$this->prepareCertificate();
		$this->createExportRequest();		
	}

	public function getRequestStatus()
	{
		return $this->requestStatus;
	}
	
	private function prepareCertificate()
	{
		// STEP 1: CHECK FOR EXTANT CERTIFICATE OFFLINE
		$myKeyManagementController = new KeyManagementController();
		$keyPairExists = $myKeyManagementController->checkForExtantKeyPair($this->requestDomain);
		if (!$keyPairExists)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' No Public+Private Keypair Exists for ' . $this->requestDomain . '; need to create a keypair');
			
			//STEP 1.A: No Extant Keypair; Create a Keypair
			$myKeyManagementController->generateKeyPair($this->requestDomain);
		}
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Public+Private Keypair Exists for ' . $this->requestDomain);
		
		$healthyKey = $myKeyManagementController->checkForKeyPairHealth($this->requestDomain);
		//TODO: implement measures to deal with key health resutls
		
		//STEP 1.B: CHECK FOR BASE64 ENCODED PUBLIC KEY
		$encodedPublicKeyExistsOffline = $myKeyManagementController->checkForExtantBase64PublicKey($this->requestDomain);
		if (!$encodedPublicKeyExistsOffline)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' No Base64 Public Keypair Exists Offline for ' . $this->requestDomain . '; need to create it');
			
			//STEP 1.B.1: No Base64 Encoded Public Key File Exists Offline;
			$myKeyManagementController->generateBase64PublicKeyFile($this->requestDomain);
		}
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Base64 Public Key Exists Offline for ' . $this->requestDomain);
		
		$healthyKey = $myKeyManagementController->checkForKeyPairHealth($this->requestDomain);
		//TODO: implement measures to deal with key health resutls
		
		// STEP 2: UPLOAD BASE64 PUBLIC KEY
		if (!$myKeyManagementController->isBase64PublicKeyAlreadyUploaded($this->requestDomain))
		{
			$publicKeyUploadedSuccessfully = $myKeyManagementController->uploadPublicKey($this->requestDomain);
			if ($publicKeyUploadedSuccessfully != true)
			{
				throw new CreateBase64PublicKeyFailureException();
			}
		}
		
		return true;
	}
	
	//TODO: Implement check on pending requests so that admins cannot have multiple pending requests; this is a way to prevent abuse and rate limit
	// Use flat file with list of emailaddress, submit time, and ID given by Google to track
	private function checkPendingRequests()
	{
		return self::PENDING_REQUEST_STATUS_NONE;
	}
	
	private function parseRequestEmail()
	{
		$requestUsername = GlobalFunctions::getUsernameFromFullEmailAddress($this->requestEmail);
		$requestDomain = GlobalFunctions::getDomainFromFullEmailAddress($this->requestEmail);
		if ($requestUsername == false || $requestDomain == false)
		{
			throw new InvalidEmailException();		
		}
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Processed Username to Export Mailbox For: ' . $requestUsername);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Processed Domainname to Export Mailbox For: ' . $requestDomain);

		$this->requestUsername = $requestUsername;
		$this->requestDomain = $requestDomain;
	}

	private function checkInputs()
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Creating mailbox export request for user ' . $this->requestEmail);
		if (!GlobalFunctions::validateFullEmailAddress($this->requestEmail))
		{
			throw new InvalidEmailException();
		}
	}
	
	private function logSuccessfulRequestToDisk()
	{
	
	}
	
	private function createExportRequest()
	{
		$identitySecret = AccessIdentityController::getPurportedItentitySecretFromHttpRequest();
		$emailAddress = AccessIdentityController::getPurportedEmailFromHttpRequest();
		$scope = "https://apps-apis.google.com/a/feeds/user/";
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Creating mailbox export request for user ' . $this->requestUsername . ' at domain ' . $this->requestDomain);
		
		$myOauthRequest = new OAuth1Request(HTTPConstants::METHOD_POST, 'https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/' . $this->requestDomain . '/' . $this->requestUsername, OAuth1Request::OAUTH_AUTHENTICATION_METHOD_QUERYSTRING);
		$myOauthRequest->setOauthToken(AccessIdentityController::getOauthAccessTokenFromFile($emailAddress, $scope));
		$myOauthRequest->setOauthTokenSecret(AccessIdentityController::getOauthAccessTokenSecretFromFile($emailAddress, $scope));

// not sure why this is here; removing it seems to do nothing
//		$myOauthRequest->overrideOauthConsumerKey(OAuthConstants::WEB_APPLICATION_CONSUMER_KEY);
//		$myOauthRequest->overrideOauthConsumerSecret(OAuthConstants::WEB_APPLICATION_CONSUMER_SECRET);
		$myOauthRequest->constructOauthRequest();
		
		$myRequest = new HTTPPostRequest();
		$myRequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
		$myRequest->setConnectionHost("apps-apis.google.com");
		$myRequest->setHttpHost("apps-apis.google.com");
		$myRequest->setPath('a/feeds/compliance/audit/mail/export/' . $this->requestDomain . '/' . $this->requestUsername);
		$myRequest->setPostData('<atom:entry xmlns:atom="http://www.w3.org/2005/Atom" xmlns:apps="http://schemas.google.com/apps/2006">
		   <apps:property name="includeDeleted" value="true"/>
		   <apps:property name="packageContent" value="FULL_MESSAGE"/>
		</atom:entry>');
		$myRequest->addHeader(new HTTPHeader('Accept', '*/*'));
		$myRequest->addHeader(new HTTPHeader('Authorization', $myOauthRequest->getFinalOauthAuthorizationHeader()));
		$myRequest->addHeader(new HTTPHeader('Content-Type', 'application/atom+xml'));
		$myRequest->addHeader(new HTTPHeader('GData-Version', '2.0'));
				
		$myRequest->executeRequest();
		
		$this->parseResponse($myRequest->getHttpResponse());
	}
	
	private function parseResponse(HTTPResponse $httpResponse)
	{
		/***EXAMPLE RESPONSE
		
		Array ( [0] => HTTPHeader Object ( [header:HTTPHeader:private] => Content-Type [value:HTTPHeader:private] => application/atom+xml; charset=UTF-8; type=entry ) [1] => HTTPHeader Object ( [header:HTTPHeader:private] => Expires [value:HTTPHeader:private] => Tue, 06 Sep 2011 05:30:46 GMT ) [2] => HTTPHeader Object ( [header:HTTPHeader:private] => Date [value:HTTPHeader:private] => Tue, 06 Sep 2011 05:30:46 GMT ) [3] => HTTPHeader Object ( [header:HTTPHeader:private] => Cache-Control [value:HTTPHeader:private] => private, max-age=0, must-revalidate, no-transform ) [4] => HTTPHeader Object ( [header:HTTPHeader:private] => Vary [value:HTTPHeader:private] => Accept, X-GData-Authorization, GData-Version ) [5] => HTTPHeader Object ( [header:HTTPHeader:private] => GData-Version [value:HTTPHeader:private] => 2.0 ) [6] => HTTPHeader Object ( [header:HTTPHeader:private] => ETag [value:HTTPHeader:private] => W/"DUcAR38yeip7ImA9WhdWEkU." ) [7] => HTTPHeader Object ( [header:HTTPHeader:private] => Location [value:HTTPHeader:private] => https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/mypremierapps.info/test11/30571436 ) [8] => HTTPHeader Object ( [header:HTTPHeader:private] => Content-Location [value:HTTPHeader:private] => https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/mypremierapps.info/test11/30571436 ) [9] => HTTPHeader Object ( [header:HTTPHeader:private] => X-Content-Type-Options [value:HTTPHeader:private] => nosniff ) [10] => HTTPHeader Object ( [header:HTTPHeader:private] => X-Frame-Options [value:HTTPHeader:private] => SAMEORIGIN ) [11] => HTTPHeader Object ( [header:HTTPHeader:private] => X-XSS-Protection [value:HTTPHeader:private] => 1; mode=block ) [12] => HTTPHeader Object ( [header:HTTPHeader:private] => Server [value:HTTPHeader:private] => GSE ) [13] => HTTPHeader Object ( [header:HTTPHeader:private] => Connection [value:HTTPHeader:private] => close ) ) https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/mypremierapps.info/test11/305714362011-09-06T05:30:46.192Z2011-09-06T05:30:46.192Z
		
		****/
		if ($httpResponse->getResponseStatusCode() == 201)
		{
			$responseHeaders = $httpResponse->getResponseHeaders();
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' HTTP Response Status Code for Mailbox Export Request for ' . $this->requestEmail . ' was: ' . $httpResponse->getResponseStatusCode());
			foreach ($responseHeaders as $header)
			{
				if ($header->getHeader() == 'Content-Location')
				{
					$exportLocation = $header->getValue();
				}
			}
			
			// TODO: Write to disk export location and success of export request;
			// Use this later on to ensure that we do not have multiple requests for a single user simultaneously occurring
			$this->requestStatus = self::REQUEST_STATUS_SUCCESS;		
		}
		else if ($httpResponse->getResponseStatusCode() == 401)
		{
			$this->requestStatus = self::REQUEST_STATUS_FAILURE;
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failed to create Mailbox Export for ' . $this->requestEmail . ' due to HTTP 401 invalid auth... set status as: ' . $this->requestStatus);
			throw new InvalidHttpAuthorization();
		}
		else if ($httpResponse->getResponseStatusCode() == 400
				&& stripos($httpResponse->getResponseContent(), 'reason="EntityDoesNotExist"') !== false)
		{
			$this->requestStatus = self::REQUEST_STATUS_FAILURE;
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failed to create Mailbox Export for ' . $this->requestEmail . ' due to HTTP 400 EntityDoesNotExist as the requested email address does not exist; Status: ' . $this->requestStatus);
			throw new InvalidEmailaddress(); 			
		}
		else
		{
			$this->requestStatus = self::REQUEST_STATUS_FAILURE;
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failed to create Mailbox Export for ' . $this->requestEmail . '; status set as: ' . $this->requestStatus);	
			throw new CreateMailboxExportRequestFailure();
		}
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Status of Mailbox Export Request for ' . $this->requestEmail . '; status set as: ' . $this->requestStatus);
	}
}

class CreateMailboxExportRequestFailure extends Exception
{
	
}

class InvalidEmailException extends Exception
{
	
}

class CreateBase64PublicKeyFailureException extends Exception
{
	
}
?>
