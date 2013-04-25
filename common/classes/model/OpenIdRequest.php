<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

/*
 * In keeping with the OpenId flow, these fucntions need to be performed in the following order in order to successfully use openid:
 * 1. performOpenIdDiscovery() -- discovery function to get the openid endpoint url
 * 2. constructFullOpenIdRequestUrl() -- performed internally; construct the customer facing openid request url
 * 
 * 
 * Supports OAuth+OpenId extension:http://code.google.com/apis/accounts/docs/OAuth.html#prepOpenID
 */
class OpenIdRequest
{
	//OPENID PHASE 1 VARIABLES
	private $openIdEndpointUrl; // discovered in phase 1, used in phase 2

	//OPENID PHASE 2 VARIABLES
	private $openIdReturnUrl;
	private $openIdRealm;
	private $openIdAxRequired;
	
	private $openIdOauth1Scope;
	private $openIdOauth1ConsumerKey;
	
	private $openIdMode = OpenIdConstants::OPENID_MODE_SETUP;
	private $openIdNs = OpenIdConstants::OPENID_NS;
	private $openIdClaimedId = OpenIdConstants::OPENID_CLAIMED_ID;
	private $openIdIdentity = OpenIdConstants::OPENID_IDENTITY;
	private $openIdNsAx = OpenIdConstants::OPENID_NS_AX;
	private $openIdAxMode = OpenIdConstants::OPENID_AX_MODE_FETCH;
	private $openIdNsOauth = OpenIdConstants::OPENID_NS_OAUTH;
	
	private $openIdUiMode;
	
	private $openIdFinalRequestUrl; //discovered in phase 2, used by HTTP frontend for user to make request

	/*
	 * Set OAuth1 consumer key, nevessary for OAuth+OpenID hybrid
	 */
	public function setOauth1ConsumerKey($key)
	{
		$this->openIdOauth1ConsumerKey = $key;
	}
	
	/*
	 * Set OAuth1 scope parameter, nevessary for OAuth+OpenID hybrid
	 */
	public function setOauth1Scope($scope)
	{
		$this->openIdOauth1Scope = $scope;
	}
	
	public function setOpenIdAxRequired($required)
	{
		$this->openIdAxRequired = $required;
	}
	
	public function setOpenIdMode($openIdMode)
	{
		if ($openIdMode == OpenIdConstants::OPENID_MODE_SETUP
			|| $openIdMode == OpenIdConstants::OPENID_MODE_IMMEDIATE)
		{
			$this->openIdMode = $openIdMode;
		}
	}
	
	public function setOpenIdUiMode($mode)
	{
		if ($mode == OpenIdConstants::OPENID_UI_MODE_SESSION)
		{
			$this->openIdUiMode = $mode;
		}
	}
	
	/*
	 * @param $url full url to which the openid request should return
	 *      if using the openid+oauth extension, this must include the "xrequested_scopes" argument
	 *      for compatability with other classes
	 */
	public function setOpenIdReturnUrl($url)
	{
		$this->openIdReturnUrl = $url;
	}
	
	public function setOpenIdRealm($realm)
	{
		$this->openIdRealm = $realm;
	}
	
	/*
	 * Step #3 of http://code.google.com/apis/accounts/docs/OpenID.html#Interaction
	 * Perform an OpenId Ennd Point Discovery; set endpoint discovery url variable
	 */
	public function performOpenIdDiscovery()
	{
		$arr_url = parse_url(OpenIdConstants::getOpenIdDiscoveryUrl());
		$discovery_url_host = $arr_url['host'];
		$discovery_url_path = $arr_url['path'];
		
		// open a socket connection on port $port - timeout: 30 sec
		$fp = fsockopen("ssl://" . $arr_url['host'], 443, $errno, $errstr, 15);
		
		$myOpenIdHTTPRequest = new HTTPGetRequest();
		$myOpenIdHTTPRequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
		$myOpenIdHTTPRequest->setConnectionHost("www.google.com");
		$myOpenIdHTTPRequest->setHttpHost("www.google.com");
		$myOpenIdHTTPRequest->setPath($discovery_url_path);
		$myOpenIdHTTPRequest->executeRequest();
		
		$myOpenIdHTTPResponse = $myOpenIdHTTPRequest->getHttpResponse();
		$responseContent = $myOpenIdHTTPResponse->getResponseContent();
		
		// parse XML return content looking for URI
		preg_match('/<URI>(http.*?)<\/URI>/', $responseContent, $matches);
		if (isset($matches[1]))
		{
			$this->openIdEndpointUrl = $matches[1];
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' OpenID Endpoint URL: ' . $this->openIdEndpointUrl);
		}
		else
		{
			throw new OpenIdEndpointDiscoveryException("No URI found in XML return");
		}
	}
	
	/*
	 * http://code.google.com/apis/accounts/docs/OpenID.html#Parameters 
	 * Construct the full openid(+oauth) request url, which the user clicks on to enter the OpenID(+OAuth flow)
	 * 
	 * It is essential to add the "xrequested_scopes" GET variable to the return/callback URL
	 * e.g.,: "xrequested_scopes=https://apps-apis.google.com/a/feeds/compliance/audit/+https://apps-apis.google.com/a/feeds/user/"
	 */
	private function constructFullOpenIdRequestUrl()
	{
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdEndpointUrl = ' . $this->openIdEndpointUrl);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdMode = ' . $this->openIdMode);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdNs = ' . $this->openIdNs);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdRealm = ' . $this->openIdRealm);
		$openid_request_url = $this->openIdEndpointUrl . "?openid.mode=" . urlencode($this->openIdMode);
		$openid_request_url .= "&openid.ns=" . urlencode($this->openIdNs);
		$openid_request_url .= "&openid.realm=" . urlencode($this->openIdRealm);
		
		//add extra "xrequested_scopes" GET variable to return/callback URL for later use by other parser
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdReturnUrl = ' . $this->openIdReturnUrl);
		$final_return_url = $this->openIdReturnUrl;
		if (isset($this->openIdOauth1Scope))
		{
			//append "xrequested_scopes" to return url
			$url_parts = parse_url($final_return_url);
			//TODO: PROPERLY IMPLEMENT THIS ?& so that webpage can be HTML strict compliant
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdOauth1Scope = ' . $this->openIdOauth1Scope);
			$final_return_url .= "?&xrequested_scopes=" . $this->openIdOauth1Scope;
		}
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdClaimedId = ' . $this->openIdClaimedId);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdIdentity = ' . $this->openIdIdentity);
		$openid_request_url .= "&openid.return_to=" . urlencode($final_return_url);
		$openid_request_url .= "&openid.claimed_id=" . urlencode($this->openIdClaimedId);
		$openid_request_url .= "&openid.identity=" . urlencode($this->openIdIdentity);
		
		//optional parameter used for stealth identity check as per http://code.google.com/apis/accounts/docs/OpenID.html
		if ($this->openIdUiMode !== null)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdUiMode = ' . $this->openIdUiMode);
			$openid_request_url .= "&openid.ui.mode=" . urlencode($this->openIdUiMode);
		}
		
		//pertinent documetnation at http://code.google.com/apis/accounts/docs/OpenID.html#Parameters
		if ($this->openIdAxRequired !== null)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdAxRequired = ' . $this->openIdAxRequired);
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdAxMode = ' . $this->openIdAxMode);
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdNsAx = ' . $this->openIdNsAx);
			$openid_request_url .= "&openid.ax.required=" . urlencode($this->openIdAxRequired);
			$openid_request_url .= "&openid.ax.mode=" . $this->openIdAxMode;
			$openid_request_url .= "&openid.ns.ax=" . $this->openIdNsAx;
			
			$arr_possible_attributes = array(
				'email' => array('attribute' => 'openid.ax.type.email', 'url_schema' => 'http://axschema.org/contact/email'), 
				'lastname' => array('attribute' => 'openid.ax.type.firstname', 'url_schema' => 'http://axschema.org/namePerson/first'),
				'firstname' => array('attribute' => 'openid.ax.type.lastname', 'url_schema' => 'http://axschema.org/namePerson/last'),
			);
		
			$arr_required_attributes = explode(",", $this->openIdAxRequired);
			foreach($arr_required_attributes as $attribute)
			{
				Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdAxRequired Attribute::' . $arr_possible_attributes[$attribute]['attribute'] . "=" . $arr_possible_attributes[$attribute]['url_schema']);
				$openid_request_url .= "&" . $arr_possible_attributes[$attribute]['attribute'] . "=" . $arr_possible_attributes[$attribute]['url_schema'];
			}
		}
		
		//check for oauth desired?
		if ($this->openIdOauth1ConsumerKey !== null && $this->openIdOauth1Scope !== null)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdNsExt2 = ' . $this->openIdNsOauth);
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdExt2Consumer = ' . $this->openIdOauth1ConsumerKey);
			Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Raw OpenID Variable Value OpenIdExt2Scope = ' . $this->openIdOauth1Scope);
			$openid_request_url .= "&openid.ns.ext2=" . $this->openIdNsOauth;
			$openid_request_url .= "&openid.ext2.consumer=" . $this->openIdOauth1ConsumerKey;
			$openid_request_url .= "&openid.ext2.scope=" . $this->openIdOauth1Scope;
		}
		
		$this->openIdFinalRequestUrl = $openid_request_url;
		Logger::add_info_log_entry(__FILE__ . __LINE__ . ' OpenID Final Request URL: ' . $this->openIdFinalRequestUrl);

		/**
		 * The following was used to make the applicatoin XHTML strict compliant, but it caused other problems
		 * $this->openIdFinalRequestUrl = str_replace("&", "&amp;", $this->openIdFinalRequestUrl);
		 */
	}
	
	/*
	 * Return the full openid(+oauth) request url that the end-user goes to to enter the authorization flow
	 * 
	 * @return String openId(+Oauth) request url
	 */
	public function getFullOpenIdRequestUrl()
	{
		$this->constructFullOpenIdRequestUrl();
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " Full OpenID Request URL: " . $this->openIdFinalRequestUrl);
		return $this->openIdFinalRequestUrl; 
	}
}

?>
