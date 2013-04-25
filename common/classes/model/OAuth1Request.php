<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

/*
 * Tools
 * 1. OAuth Signature and Base String Checker: http://oauth.googlecode.com/svn/code/javascript/example/signature.html
 * 2. APIs Supporting 2-Legged OAuth: http://www.google.com/support/a/bin/answer.py?hl=en&answer=162105
 * 
 * Resources
 * How to test 2-legged oauth http://groups.gooauth_callback.php?openid.ns=http%3A%2F%2Fspecs.openid.net%2Faogle.com/group/google-accounts-api/browse_thread/thread/c9578196c6fed9a2?pli=1
 */
class OAuth1Request
{
	//CLASS VARIABLES
	const OAUTH_AUTHENTICATION_METHOD_QUERYSTRING = 'AUTHENTICATION_METHOD_QUERYSTRING';
	const OAUTH_AUTHENTICATION_METHOD_HEADER = 'AUTHENTICATION_METHOD_HEADER';	

	//INPUT VARIABLES
	private $http_request_method;
	private $full_http_input_url;
	
	//OAUTH VARIABLES
	private $oauth_consumer_key;
	private $oauth_consumer_secret;
	private $oauth_version;
	private $oauth_nonce;
	private $oauth_timestamp;
	private $oauth_authentication_method;
	private $oauth_token;
	private $oauth_token_secret;
	
	//INTERMEDIATE VALUES
	private $all_potential_querystring_values;
	
	//OUTPUT VARIABLES
	private $final_base_string;
	private $final_full_request_url;
	private $oauth_signature;
	private $oauth_urlunencoded_signature;
	private $oauth_header_data_variables_string;
	
	/*
	 * @param $http_request_method The HTTP request method (e.g., GET, POST)
	 * @param $full_http_url The full http request URL including the entire pathname and querystring
	 * 	Do not include any Oauth variables
	 * @param $authentication_method The means of authenticating the Oauth HTTP request (e.g., querystring or HTTP header)
	 */
	function __construct($http_request_method, $full_http_url, $authentication_method)
	{
		//SET OAUTH VARIABLES TO DEFAULT GLOBAL VARIABLES
		$this->oauth_timestamp = mktime();
		$this->oauth_nonce = $this->generateOauthNonce();
		$this->oauth_consumer_key = OAuthConstants::getGAppsDomainControlPanelConsumerKey();
		$this->oauth_conusmer_secret = OAuthConstants::getGAppsDomainControlPanelConsumerSecret();
		$this->oauth_version = OAuthConstants::OAUTH_VERSION;
	
		//SET INPUT VARIABLES
		$this->http_request_method = $http_request_method;
		$this->full_http_input_url = $full_http_url;
		$this->oauth_authentication_method = $authentication_method;
	}
	
	/*
	 * Take the OAuth parameters set in the constructor and the setter methods
	 * and construct the OAuth request parameters, storing them in values to be accessed later by getter methods
	 */
	public function constructOauthRequest()
	{
		//STEP 0: Logging
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " OAuth Timestamp: " . $this->oauth_timestamp);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " OAuth Nonce: " . $this->oauth_nonce);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " OAuth Consumer Key: " . $this->oauth_consumer_key);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " OAuth Consumer Secret: " . $this->oauth_conusmer_secret);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " OAuth Version: " . $this->oauth_version);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " OAuth Signature Method: " . OAuthConstants::OAUTH_SIGNATURE_METHOD);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " OAuth Token: " . $this->oauth_token);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " OAuth Token Secret: " . $this->oauth_token_secret);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " OAuth Request Method: " . $this->http_request_method);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " OAuth Request URL: " . $this->full_http_input_url);
		
		//STEP 1: GENERATE OAUTH BASE STRING
		//Step 1.1: Generate Data Variables Elgiable for Base String (including input querystring and other data)
		$this->final_base_string = $this->get_signature_base_string();
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " OAauth Base String: " . $this->final_base_string);
		
		//STEP 2: GENERATE OAUTH SIGNATURE
		$this->oauth_urlunencoded_signature = $this->generate_urlunencoded_oauth1_signature();
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " URL Unencoded Oauth Signature: " . $this->oauth_urlunencoded_signature);
		
		$this->oauth_signature = urlencode($this->oauth_urlunencoded_signature);
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " URL Encoded Oauth Signature: " . $this->oauth_signature);
		
		//Step 2.1: Add Signature to Potential Qurystring Variables Array
		// as the oauth_signature could be passed in the HTTP querystring
		$this->all_potential_querystring_values['oauth_signature'] = $this->oauth_signature;
		ksort($this->all_potential_querystring_values);
	}
	
	/*
	 * Return the final and full HTTP request URL to connect to with the HTTP request.
	 * Take into account the OAuth authentication method in constructing the URL.
	 * 
	 */
	public function getFinalFullRequestUrl()
	{
		if ($this->oauth_authentication_method == OAuth1Request::OAUTH_AUTHENTICATION_METHOD_HEADER)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " URL Final OAuth Request URL: " . $this->full_http_input_url);
			return $this->full_http_input_url;
		}
		else if ($this->oauth_authentication_method == OAuth1Request::OAUTH_AUTHENTICATION_METHOD_QUERYSTRING)
		{
			$parts = parse_url($this->full_http_input_url);
	    
			$scheme = (isset($parts['scheme'])) ? $parts['scheme'] : 'http';
		    $host = (isset($parts['host'])) ? strtolower($parts['host']) : '';
		    $path = (isset($parts['path'])) ? $parts['path'] : '';
		
	    	$return_url = $scheme . '://' . $host . $path . "?";
	    
			foreach($this->all_potential_querystring_values as $key => $value)
			{
				$return_url .= $key . '=' . $value . '&';
			}
			
			$return_url = substr($return_url, 0, -1);
			
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " URL Final OAuth Request URL: " . $return_url);
			return $return_url;
		}
	}
	
	/*
	 * Return the final OAuth authorization header to be used to authenticate in the HTTP request
	 */
	public function getFinalOauthAuthorizationHeader()
	{
		$return_header = 'OAuth ';
		foreach($this->all_potential_querystring_values as $key => $value)
		{
			if (preg_match('/^oauth_/', $key) > 0)
			{
				$return_header .= $key . '="' . $value . '", ';
			}
		}
		
		$return_header = substr($return_header, 0, -2);
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " URL Final OAuth Request Authorization Header: " . $return_header);
		return $return_header;
	}
	
	/*
	 * 
	 */
	public function setOauthToken($token)
	{
		$this->oauth_token = $token;
	}

	/*
	 * 
	 */
	public function setOauthTokenSecret($token_secret)
	{
		$this->oauth_token_secret = $token_secret;
	}
	
	/*
	 * @param $key
	 */
	public function overrideOauthConsumerKey($key)
	{
		$this->oauth_consumer_key = $key;
	}
	
	/*
	 * @param $secret
	 */
	public function overrideOauthConsumerSecret($secret)
	{
		$this->oauth_conusmer_secret = $secret;
	}
	
	/*
	 * @param $version
	 */
	public function overrideOauthVersion($version)
	{
		$this->oauth_version = $version;
	}
	
	/*
	 * DEBUGGING FUNCTION
	 */
	public function overrideOauthNonce($nonce)
	{
		$this->oauth_nonce = $nonce;
	}

	/*
	 * DEBUGGING FUNCTION
	 */
	public function overrideOauthTimestamp($timestamp)
	{
		$this->oauth_timestamp = $timestamp;
	}
	
	/*
	 * DEBUGGING FUNCTION
	 */
	public function getOauthBaseString()
	{
		return $this->final_base_string;
	}
	
	/*
	 * DEBUGGING FUNCTION
	 */
	public function getUrlencodedOauthSignature()
	{
		return $this->oauth_signature;
	}

	/*
	 * DEBUGGING FUNCTION
	 */
	public function getUrlunencodedOauthSignature()
	{
		return $this->oauth_urlunencoded_signature;
	}
	
	/*
	 * Generate a "Random 64-bit, unsigned number encoded as an ASCII string in decimal format.
	 *  The nonce/timestamp pair should always be unique to prevent replay attacks."
	 */
	private function generateOauthNonce()
	{
		return md5(uniqid(rand(), true));
	}
  
	private function build_http_query($params)
	{
    	if (!$params) return '';

	    // Urlencode both keys and values
	    $keys = OAuthUtil::urlencode_rfc3986(array_keys($params));
	    $values = OAuthUtil::urlencode_rfc3986(array_values($params));
	    $params = array_combine($keys, $values);

	    // Parameters are sorted by name, using lexicographical byte value ordering.
	    // Ref: Spec: 9.1.1 (1)
	    uksort($params, 'strcmp');
	
	    $pairs = array();
	    foreach ($params as $parameter => $value)
	    {
		      if (is_array($value))
		      {
		        // If two or more parameters share the same name, they are sorted by their value
		        // Ref: Spec: 9.1.1 (1)
		        // June 12th, 2010 - changed to sort because of issue 164 by hidetaka
		        sort($value, SORT_STRING);
		        foreach ($value as $duplicate_value)
		        {
					$pairs[] = $parameter . '=' . $duplicate_value;
				}
			}
			else
			{
				$pairs[] = $parameter . '=' . $value;
			}
	    }

	    // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
	    // Each name-value pair is separated by an '&' character (ASCII code 38)
	    return implode('&', $pairs);
	}
	
	/*
	 * @param $parameters
	 */
	private function get_signable_parameters($parameters)
	{
	    // Grab all parameters
	    $params = $parameters;
	
	    // Remove oauth_signature if present
	    // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
	    if (isset($params['oauth_signature']))
	    {
			unset($params['oauth_signature']);
	    }
	
	    return $this->build_http_query($params);
	}
	
	/*
	 * @param $input_url
	 */
	private function get_normalized_http_url($input_url)
	{
	    $parts = parse_url($input_url);
	
	    $scheme = (isset($parts['scheme'])) ? $parts['scheme'] : 'http';
	    $port = (isset($parts['port'])) ? $parts['port'] : (($scheme == 'https') ? '443' : '80');
	    $host = (isset($parts['host'])) ? strtolower($parts['host']) : '';
	    $path = (isset($parts['path'])) ? $parts['path'] : '';
	
	    if (($scheme == 'https' && $port != '443')
	        || ($scheme == 'http' && $port != '80')) {
	      $host = "$host:$port";
	    }
	    return "$scheme://$host$path";
	}
	
	/*
	 */
	private function get_signature_base_string()
	{
		$all_parameters = array();
	
		//construct associative array of all querystring key=value pairs
		parse_str(parse_url($this->full_http_input_url, PHP_URL_QUERY), $all_parameters);

		//add oauth parameters present in all requests
		$all_parameters['oauth_version'] = $this->oauth_version;
		$all_parameters['oauth_consumer_key'] = $this->oauth_consumer_key;
		$all_parameters['oauth_nonce'] = $this->oauth_nonce;
		$all_parameters['oauth_signature_method'] = OAuthConstants::OAUTH_SIGNATURE_METHOD;
		$all_parameters['oauth_timestamp'] = $this->oauth_timestamp;
		
		
		//check for and add if necessary oauth parameters present in some requests
		if (isset($this->oauth_token))
		{
			$all_parameters['oauth_token'] = $this->oauth_token;
		}
		
		// store for later use associative array of all parameters that could be in the querystring
		$this->all_potential_querystring_values = $all_parameters; 
		
		$parts = array(
			strtoupper($this->http_request_method),
			$this->get_normalized_http_url($this->full_http_input_url),
			$this->get_signable_parameters($this->all_potential_querystring_values)
	    );
	
	    $parts = OAuthUtil::urlencode_rfc3986($parts);
	
	    return implode('&', $parts);
	}
	
	/*
	 * Return a URL encoded oauth signature
	 * 
	 */
	private function generate_urlunencoded_oauth1_signature()
	{
		$key_parts = array(
			$this->oauth_conusmer_secret,
			isset($this->oauth_token_secret) ? $this->oauth_token_secret : ""
		);

		$key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
		$key = implode('&', $key_parts);
		
		return base64_encode(hash_hmac('sha1', $this->final_base_string, $key, true));
	}
}

?>
