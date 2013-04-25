<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class OAuthAccessTokenResponse extends OAuthResponse
{
	private $oauth_token;
	private $oauth_token_secret;
	
	private $http_response;

	function __construct(HTTPResponse $httpResponse)
	{
		$this->http_response = $httpResponse;
		$this->parseResponse();
	}
	
	private function parseResponse()
	{
		if ($this->http_response->getResponseStatusCode() != 200)
		{
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " OAuth Access Token Response Error: " . $this->http_response->getResponseContent());
			throw new OAuthAccessTokenResponseException($this->http_response->getResponseContent());
		}
		
		/*
		 * Raw Successful Response: "oauth_token=1%2Fb6kdbEo-RqK3HFYvDUCIdHIMRMcPhCOrOhVKtjKAoYA&oauth_token_secret=BhizVUTaKxI9YFi9s1PVKLsb"
		 */
		if (preg_match('/oauth_token=(.*?)&oauth_token_secret=(.*?)/', $this->http_response->getResponseContent()) == 0)
		{
			//invalid or erroring response
			throw new OAuthAccessTokenResponseException();
		}
		
		$arr_token = array();
		if (preg_match('/oauth_token=(.*?)&/', $this->http_response->getResponseContent(), $arr_token))
		{
			$this->oauth_token = urldecode($arr_token[1]);
		}

		$arr_token_secret = array();
		if (preg_match('/oauth_token_secret=(.*?)$/', $this->http_response->getResponseContent(), $arr_token_secret))
		{
			$this->oauth_token_secret = urldecode($arr_token_secret[1]);
		}
	}
	
	public function getOAuthToken()
	{
		return $this->oauth_token;
		
	}
	
	public function getOAuthTokenSecret()
	{
		return $this->oauth_token_secret;
	}
}

?>
