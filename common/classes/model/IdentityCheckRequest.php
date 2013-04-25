<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class IdentityCheckRequest
{
	private $identity_check_url;
	
	function __construct($callback_url)
	{
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Callback URL for Identity Check Request: ' . $callback_url);
		
		$myOpenIdSession = new OpenIdRequest();
		$myOpenIdSession->setOpenIdAxRequired('email,firstname,lastname');
		$myOpenIdSession->setOpenIdReturnUrl($callback_url);
		$myOpenIdSession->setOpenIdRealm('https://www.apps-apps.info');
		$myOpenIdSession->setOpenIdMode(OpenIdConstants::OPENID_MODE_IMMEDIATE);
		$myOpenIdSession->setOpenIdUiMode(OpenIdConstants::OPENID_UI_MODE_SESSION);
		
		$myOpenIdSession->performOpenIdDiscovery();
		$this->identity_check_url = $myOpenIdSession->getFullOpenIdRequestUrl();
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' OpenID Identity Check URL: ' . $this->identity_check_url);
	}
	
	/*
	 * Return the url that the user's browser should be redirected to in order to conduct an identity check request
	 * 
	 * @return String url that the browser should visit to perform Google identity check
	 */
	public function getIdentityCheckUrl()
	{
		return $this->identity_check_url;
	}
}

?>
