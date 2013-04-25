<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class MailboxExportStatusRequestController
{
	private $fromDate;
	private $exportStatusList;

	function __construct()
	{
		$this->fromDate = date("Y-m-d G:i", mktime() - 60*60*31);
		$this->exportStatusList = new MailboxExportStatusList();
	}

	public function retrieveMailboxExportStatusList()
	{
		$emailAddress = AccessIdentityController::getPurportedEmailFromHttpRequest();
		$domainName = GlobalFunctions::getDomainFromFullEmailAddress($emailAddress);
		$identitySecret = AccessIdentityController::getPurportedItentitySecretFromHttpRequest();
		$scope = "https://apps-apis.google.com/a/feeds/user/";
		$fromDate = urlencode(date("Y-m-d G:i", mktime() - 60*60*24*31));
		
		$myOauthRequest = new OAuth1Request(HTTPConstants::METHOD_GET, 'https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/' . $domainName . '?fromDate=' . $fromDate, OAuth1Request::OAUTH_AUTHENTICATION_METHOD_QUERYSTRING);
		$myOauthRequest->setOauthToken(AccessIdentityController::getOauthAccessTokenFromFile($emailAddress, $scope));
		$myOauthRequest->setOauthTokenSecret(AccessIdentityController::getOauthAccessTokenSecretFromFile($emailAddress, $scope));

// not sure why this is here; removing it seems to do nothing
//		$myOauthRequest->overrideOauthConsumerKey(OAuthConstants::WEB_APPLICATION_CONSUMER_KEY);
//		$myOauthRequest->overrideOauthConsumerSecret(OAuthConstants::WEB_APPLICATION_CONSUMER_SECRET);
		$myOauthRequest->constructOauthRequest();
		
		$myRequest = new HTTPGetRequest();
		$myRequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
		$myRequest->setConnectionHost("apps-apis.google.com");
		$myRequest->setHttpHost("apps-apis.google.com");
		$myRequest->setPath('a/feeds/compliance/audit/mail/export/' . $domainName . '?fromDate=' . $fromDate);
		$myRequest->addHeader(new HTTPHeader('Accept', '*/*'));
		$myRequest->addHeader(new HTTPHeader('Authorization', $myOauthRequest->getFinalOauthAuthorizationHeader()));
		$myRequest->addHeader(new HTTPHeader('Content-Type', 'application/atom+xml'));
		$myRequest->addHeader(new HTTPHeader('GData-Version', '2.0'));
		$myRequest->executeRequest();
		
		if ($myRequest->getHttpResponse()->getResponseStatusCode() != 200)
		{
			Logger::add_error_log_entry(__FILE__ . __LINE__ . ' Failure to obtain list of mailbox export status, received: ' . $myRequest->getHttpResponse()->getResponseStatusCode());	
			if ($myRequest->getHttpResponse()->getResponseStatusCode() == 401)
			{
				throw new InvalidHttpAuthorization();
			}
			else if ($myRequest->getHttpResponse()->getResponseStatusCode() == 403
				&& stripos($myRequest->getHttpResponse()->getRawResponse(), "HTTP/1.1 403 Domain cannot use") !== false)
			{
				throw new DomainCannotUseApiException();
			}
			else if ($myRequest->getHttpResponse()->getResponseStatusCode() == 403
				&& stripos($myRequest->getHttpResponse()->getRawResponse(), "HTTP/1.1 403 You are not authorized to access this API") !== false)
			{
				throw new UserCannotUseApiException();
			}
			Logger::add_warning_log_entry(__FILE__ . __LINE__ . ' Failed processed obtain list of mailbox export status, received: ' . $myRequest->getHttpResponse()->getResponseStatusCode());
			throw new MailboxExportStatusRequestExceptoin();
		}
		
		$this->parseExportRequestResponse($myRequest->getHttpResponse());
		return $this->exportStatusList;
	}

	private function parseExportRequestResponse(HTTPResponse $myResponse)
	{
		/*
Examle of Response With No Export Requests
<?xml version='1.0' encoding='UTF-8'?>
<feed xmlns='http://www.w3.org/2005/Atom' xmlns:openSearch='http://a9.com/-/spec/opensearch/1.1/' xmlns:apps='http://schemas.google.com/apps/2006'>
<id>https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/apps-email.info</id><updated>2011-10-15T15:54:31.520Z</updated>
<link rel='http://schemas.google.com/g/2005#feed' type='application/atom+xml' href='https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/apps-email.info'/>
<link rel='http://schemas.google.com/g/2005#post' type='application/atom+xml' href='https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/apps-email.info'/>
<link rel='self' type='application/atom+xml' href='https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/apps-email.info?fromDate=2011-10-14+1%3A54'/>
<openSearch:startIndex>1</openSearch:startIndex>
</feed>

Example of Response With Multiple Export Requests
<?xml version='1.0' encoding='UTF-8'?>
<feed xmlns='http://www.w3.org/2005/Atom' xmlns:openSearch='http://a9.com/-/spec/opensearch/1.1/' xmlns:apps='http://schemas.google.com/apps/2006'>
<id>https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/apps-email.info</id>
<updated>2011-10-15T15:57:01.893Z</updated>
<link rel='http://schemas.google.com/g/2005#feed' type='application/atom+xml' href='https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/apps-email.info'/>
<link rel='http://schemas.google.com/g/2005#post' type='application/atom+xml' href='https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/apps-email.info'/>
<link rel='self' type='application/atom+xml' href='https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/apps-email.info?fromDate=2011-10-14+1%3A57'/>
<openSearch:startIndex>1</openSearch:startIndex>

<entry xmlns:gd='http://schemas.google.com/g/2005' gd:etag='W/"DkUGQHYyeyp7ImA9WhdbFkQ."'>
<id>https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/apps-email.info/35027857</id>
<updated>2011-10-15T15:57:01.893Z</updated>
<app:edited xmlns:app='http://www.w3.org/2007/app'>2011-10-15T15:57:01.893Z</app:edited>
<link rel='self' type='application/atom+xml' href='https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/apps-email.info/35027857'/>
<link rel='edit' type='application/atom+xml' href='https://apps-apis.google.com/a/feeds/compliance/audit/mail/export/apps-email.info/35027857'/>
<apps:property name='packageContent' value='FULL_MESSAGE'/>
<apps:property name='includeDeleted' value='true'/>
<apps:property name='status' value='PENDING'/>
<apps:property name='requestId' value='35027857'/>
<apps:property name='userEmailAddress' value='test1@apps-email.info'/>
<apps:property name='adminEmailAddress' value='administrator@apps-email.info'/>
<apps:property name='requestDate' value='2011-10-15 15:55'/>
</entry>

<entry xmlns:gd='http://schemas.google.com/g/2005' gd:etag='W/"DkUGQHYyeyp7ImA9WhdbFkQ."'>
<id>https://apps-apis.google.com/a/feeds/compliance/audit/account/example.com/53156</id>
<updated>2010-03-17T15:29:21.064Z</updated>
<link rel='self' type='application/atom+xml' href='https://apps-apis.google.com/a/feeds/compliance/audit/account/example.com/53156'/>
<link rel='edit' type='application/atom+xml' href='https://apps-apis.google.com/feeds/compliance/audit/account/example.com/53156'/>
<apps:property name='numberOfFiles' value='1'/>
<apps:property name='packageContent' value='FULL_MESSAGE'/>
<apps:property name='completedDate' value='2010-02-06 03:28'/>
<apps:property name='adminEmailAddress' value='admin@example.com'/>
<apps:property name='status' value='COMPLETED'/>
<apps:property name='requestId' value='53156'/>
<apps:property name='fileUrl0' value='https://apps-apis.google.com/a/data/compliance/audit/OpCKXdru3FwK6thWAqc64Jcy3X8QdoRcaCM9nXjkbeRgLjrYZ0_XjJ'/>
<apps:property name='userEmailAddress' value='abhishek@example.com'/>
<apps:property name='requestDate' value='2010-02-06 02:40'/>
</entry>


</feed>
		 */
		$responseContent = $myResponse->getResponseContent();
		
		$feed = new SimpleXMLElement($responseContent);
		if (!isset($feed->entry))
		{
			return false;	
		}
		
		foreach($feed->entry as $item)
		{
			$status = new MailboxExportStatus();
			$ns_dc = $item->children('http://schemas.google.com/apps/2006');
			foreach ($ns_dc->property as $property)
			{	
				$propertyArrtibutes = $property->attributes();
				switch ($propertyArrtibutes['name'])
				{
					case 'status': 
						$status->setStatus($propertyArrtibutes['value']);
						break;
						
					case 'adminEmailAddress': 
						$status->setAdminEmailAddress($propertyArrtibutes['value']);
						break;

					case 'userEmailAddress': 
						$status->setUserEmailAddress($propertyArrtibutes['value']);
						break;
						
					case 'requestDate':
						$status->setRequestDate($propertyArrtibutes['value']);
						break;

					case 'requestId':
						$status->setRequestId($propertyArrtibutes['value']);
						break;
					
					case 'fileUrl0':
						$status->setFileUrl($propertyArrtibutes['value']);
						break;
						
					case 'completedDate':
						$status->setCompletedDate($propertyArrtibutes['value']);
						break;
						
					case 'expiredDate':
						$status->setExpiredDate($propertyArrtibutes['value']);
						break;
				}
				
				Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Parsed Mailbox Export Status Response Variable ' . $propertyArrtibutes['name'] . ' With Value ' . $propertyArrtibutes['value']);
			}
			$this->exportStatusList->addMailboxExportStatus($status);
		}
	}
}

class MailboxExportStatusRequestExceptoin extends Exception
{

}

?>
