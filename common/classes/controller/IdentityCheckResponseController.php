<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

/*

Raw GET variables from openid response

Array
(
    [openid_ns] => http://specs.openid.net/auth/2.0
    [openid_mode] => id_res
    [openid_op_endpoint] => https://www.google.com/accounts/o8/ud
    [openid_response_nonce] => 2011-09-26T03:25:23ZbpEcxJKw4XnBrQ
    [openid_return_to] => https://www.apps-apps.info/emailarchive/identity_check_response.php
    [openid_assoc_handle] => AOQobUdWt36hjUZfCgcgeLVm3hi9K5VWkMjIMd5zEJeA1lxiIF5fFXEJ
    [openid_signed] => op_endpoint,claimed_id,identity,return_to,response_nonce,assoc_handle,ns.ext1,ext1.mode,ext1.type.firstname,ext1.value.firstname,ext1.type.email,ext1.value.email,ext1.type.lastname,ext1.value.lastname
    [openid_sig] => zvB3pQrHJ4HpU+Otm7vTIK9NhVE=
    [openid_identity] => https://www.google.com/accounts/o8/id?id=AItOawn6MN02uIKi30uQYAJ_auhCjepuugTtI-Q
    [openid_claimed_id] => https://www.google.com/accounts/o8/id?id=AItOawn6MN02uIKi30uQYAJ_auhCjepuugTtI-Q
    [openid_ns_ext1] => http://openid.net/srv/ax/1.0
    [openid_ext1_mode] => fetch_response
    [openid_ext1_type_firstname] => http://axschema.org/namePerson/first
    [openid_ext1_value_firstname] => Test
    [openid_ext1_type_email] => http://axschema.org/contact/email
    [openid_ext1_value_email] => administrator@mypremierapps.info
    [openid_ext1_type_lastname] => http://axschema.org/namePerson/last
    [openid_ext1_value_lastname] => User
)

*** OR ***
*
Array
(
    [openid_mode] => cancel
    [openid_ns] => http://specs.openid.net/auth/2.0
)

 */
class IdentityCheckResponseController
{
	private $identityResponse;
	private $identityValidated = false;
	
	private $validResponseEmail;
	private $validResponseFirstName;
	private $validResponseLastName;
	private $validResponseClaimedId;
	
	function __construct(IdentityCheckResponse $response)
	{
		$this->identityResponse = $response;
		$this->checkIdentity();
		
		if ($this->identityValidated == true)
		{
			$this->updateIdentityRepository();
		}
		
		$this->updateCookies();
	}
	
	//TODO: Potentially bad bug if users have multi-login or access to multiple accounts since someone could
	// login using one account (have the IdentitySecretCookies) use openId to validate as another user and return back to my site validated as that other second user able to take actions on the original account since they would have the original account's Identity Secret; solution to this seems to be to wipe out email and identity secret when checking openId
	private function checkIdentity()
	{
		if ($this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_email')
			&& $this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_firstname')
			&& $this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_lastname')
			&& $this->identityResponse->getOpenIdResponseVariable('openid_claimed_id')
			&& OpenIdOAuthResponseValueValidation::validateEmailAddress($this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_email'))
			&& OpenIdOAuthResponseValueValidation::validateFirstOrLastName($this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_firstname'))
			&& OpenIdOAuthResponseValueValidation::validateFirstOrLastName($this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_lastname'))
			&& OpenIdOAuthResponseValueValidation::validateClaimedId($this->identityResponse->getOpenIdResponseVariable('openid_claimed_id'))
			&& strpos($this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_email'), '@') !== false
			&& $this->checkClaimedIdMismatch($this->identityResponse->getOpenIdResponseVariable('openid_claimed_id')) == false
		)
		{
			$this->identityValidated = true;
			$this->validResponseEmail = $this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_email');
			$this->validResponseFirstName = $this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_firstname');
			$this->validResponseLastName = $this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_lastname');
			$this->validResponseClaimedId = $this->identityResponse->getOpenIdResponseVariable('openid_claimed_id');
		}
		else if ($this->identityResponse->getOpenIdResponseVariable('openid_mode') == "cancel")
		{
			$this->identityValidated = false;
		}
	}

	public function checkClaimedIdMismatch($claimedIdFromHttpResponse)
	{
		// STEP 1: Determine if IdentityRepositoryEntry exists on file; if it does, check against claimedId; if it does not, return this false since there is no mismatch since there is no record on file
		try {
			$claimedIdOnFile = AccessIdentityController::getOpenIdClaimedIdFromFile($this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_email'));
		} catch (NoSuchUserException $e) {
			// user does not exist on file; we cannot check a claimedID, so return false since there is no mismatch
			return false;
		}

		// STEP 2: Check new claimed ID
		if ($claimedIdOnFile == $claimedIdFromHttpResponse) {
			return false;
		} else {
			// mismatch in claimed id; potential attach
			Logger::add_warning_log_entry(__FILE__ . __LINE__ . ' Identity validated check failed due to claimedId mismatch. claimedId on file: ' . $claimedIdOnFile . ' while claimedId from HTTP: ' . $claimedIdFromHttpResponse);
			return true;
		}

	}

	public function isIdentityValidated()
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Identity validated check returning value of: ' . ((($this->identityValidated) === true) ? 'true' : 'false'));
		return $this->identityValidated;
	}
	
	private function updateCookies()
	{
		//STEP 1: REVOKE IDENTITY-SECRET+EMAIL COOKIE IF IDENTITY INVALID
		if ($this->identityValidated == false)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Identity validated check was false, so deleting identity and email cookies');
			AccessIdentityController::revokeIdentitySecretCookie();
			AccessIdentityController::revokeEmailCookie();			
			return false;
		}

		//STEP 2: SET UP-TO-DATE IDENTITY-SECRET+EMAIL COOKIE
		//TODO: What if the openId check ID flow somehow gets triggered without a Identity Secret already on file 
		$identitySecret = AccessIdentityController::getValidIdentitySecretByEmail($this->validResponseEmail); 
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' IdentityCheckResponseController::updateCookies() fetched the following Identity Secret fetched from disk for user ' . $this->validResponseEmail . ':: ' . $identitySecret);
		
		AccessIdentityController::setEmailCooke($this->validResponseEmail);
		AccessIdentityController::setIdentitySecretCookie($identitySecret);
	}
	
	private function updateIdentityRepository()
	{		
		$myIdentity = new Identity();
		$myIdentity->setIdentityVariable(IdentityConstants::FIRST_NAME, $this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_lastname'));
		$myIdentity->setIdentityVariable(IdentityConstants::LAST_NAME, $this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_firstname'));
		$myIdentity->setIdentityVariable(IdentityConstants::EMAIL_ADDRESS, $this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_email'));
		$myIdentity->setIdentityVariable(IdentityConstants::CLAIMED_ID, $this->identityResponse->getOpenIdResponseVariable('openid_claimed_id'));

		if ($this->identityResponse->getOpenIdResponseVariable('domain') == GlobalFunctions::getDomainFromFullEmailAddress($this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_email')))  {
			$myIdentity->setIdentityVariable(IdentityConstants::MARKETPLACE_INSTALLED, '1');
		}

		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' IdentityCheckResponseController::updateIdentityRepository() creating updated Identity to be passed for disk writing with credentials of lastname --' . $this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_lastname') . '-- and firstname of --' . $this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_firstname') . '-- and email of --' . $this->identityResponse->getOpenIdResponseVariable('openid_ext1_value_email') . '-- and claimed ID of--' . $this->identityResponse->getOpenIdResponseVariable('openid_claimed_id'));
		
		$myIdentityRepositoryHelper = new IdentityRepositoryHelper();
		$myIdentityRepositoryHelper->setIdentity($myIdentity);
		$myIdentityRepositoryHelper->storeIdentityRepositoryToDisk();
	}
}
?>
