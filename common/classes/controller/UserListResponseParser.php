<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class UserListResponseParser
{
	private $userList;

	function __construct(HTTPResponse $response)
	{
		if ($response->getResponseStatusCode() == 401)
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failure to parse user list due to 401 status code: ' . $response->getResponseContent());	
			throw new InvalidHttpAuthorization();
		}
		else if ($response->getResponseStatusCode() == 403
			&& stripos($response->getRawResponse(), "HTTP/1.1 403 Domain cannot use") !== false)
		{
			throw new DomainCannotUseApiException();
		}
		else if ($response->getResponseStatusCode() == 403
			&& stripos($response->getRawResponse(), "HTTP/1.1 403 You are not authorized to access this API") !== false)
		{
			throw new UserCannotUseApiException();
		}
		
		$this->userList = new UserList();
		
		/*
		 * RETURN FOR API CALL DESIGNED FOR SINGLE-DOMAIN:
		 * <title>eric</title><link rel='self' type='application/atom+xml' href='https://apps-apis.google.com/a/feeds/apps-email.info/user/2.0/eric'/>
		 * 
		 * RETURN FOR API CALL DESIGNED FOR MULTI-DOMAIN:
		 * Full: <id>https://apps-apis.google.com/a/feeds/user/2.0/apps-email.info/big%40apps-email.info</id><updated>2011-10-04T06:21:20.774Z</updated><app:edited xmlns:app='http://www.w3.org/2007/app'>2011-10-04T06:21:20.774Z</app:edited><link rel='self' type='application/atom+xml' href='https://apps-apis.google.com/a/feeds/user/2.0/apps-email.info/big%40apps-email.info'/><link rel='edit' type='application/atom+xml' href='https://apps-apis.google.com/a/feeds/user/2.0/apps-email.info/big%40apps-email.info'/><apps:property name='lastName' value='Account'/><apps:property name='isChangePasswordAtNextLogin' value='false'/><apps:property name='agreedToTerms' value='true'/><apps:property name='isSuspended' value='false'/><apps:property name='userEmail' value='big@apps-email.info'/><apps:property name='isAdmin' value='false'/><apps:property name='firstName' value='Big'/><apps:property name='ipWhitelisted' value='false'/></entry><entry xmlns:gd='http://schemas.google.com/g/2005' gd:etag='W/"A0UMQXk8fCp7ImA9WhdUF00."'>
		 * Pertinent: <link rel='edit' type='application/atom+xml' href='https://apps-apis.google.com/a/feeds/user/2.0/apps-email.info/big%40apps-email.info'/><apps:property name='lastName' value='Account'/><apps:property name='isChangePasswordAtNextLogin' value='false'/><apps:property name='agreedToTerms' value='true'/><apps:property name='isSuspended' value='false'/><apps:property name='userEmail' value='big@apps-email.info'/><apps:property name='isAdmin' value='false'/><apps:property name='firstName' value='Big'/>
		 */
		preg_match_all("~<link rel='edit' type='application/atom\+xml' href='https://apps-apis\\.google\\.com/a/feeds/user/2\\.0/[^/]+/([^%]+)%40([^']+)'/><apps:property name='lastName' value='([^']+)'/>.*?<apps:property name='firstName' value='([^']+)'/>~", $response->getResponseContent(), $matches, PREG_SET_ORDER);
		foreach ($matches as $match)
		{
			$myUser = new User($match[1] . '@' . $match[2]);
			$myUser->setFirstname($match[4]);
			$myUser->setLastname($match[3]);
			$this->userList->addUser($myUser);
		}
	}
	
	public function getUserList()
	{
		if ($this->userList instanceof UserList)
		{
			return $this->userList;
		}
		else
		{
			throw new UserListResponseParserException();
		}
	}
}

class UserListResponseParserException extends Exception
{

}

?>
