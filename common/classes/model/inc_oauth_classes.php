<?php 
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('OAuthConstants.php');
require_once('OAuthUtil.php');
require_once('OAuthResponse.php');
require_once('OAuthAccessTokenResponseException.php');
require_once('OAuthAccessTokenResponse.php');
require_once('OAuth1Request.php');

/**** START EXAMPLE OF 2-LEGGED OAUTH CREATING DOCUMENT

$myOauthRequest = new OAuth1Request(HTTPConstants::METHOD_POST, 'https://docs.google.com/feeds/documents/private/full?xoauth_requestor_id=administrator@apps-email.info', OAuth1Request::OAUTH_AUTHENTICATION_METHOD_HEADER);
$myOauthRequest->constructOauthRequest();

$myrequest = new HTTPPostRequest();
$myrequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
$myrequest->setConnectionHost("docs.google.com");
$myrequest->setHttpHost("docs.google.com");
$myrequest->setPath("/feeds/documents/private/full?xoauth_requestor_id=administrator@apps-email.info");
$myrequest->setPostData('<atom:entry xmlns:atom="http://www.w3.org/2005/Atom">
  <atom:category scheme="http://schemas.google.com/g/2005#kind"
                 term="http://schemas.google.com/docs/2007#document" />
  <atom:title>Company Perks2</atom:title>
</atom:entry>');
$myrequest->addHeader(new HTTPHeader('Content-Type', 'application/atom+xml'));
$myrequest->addHeader(new HTTPHeader('Authorization', $myOauthRequest->getFinalOauthAuthorizationHeader()));
$myrequest->executeRequest();

END EXAMPLE OF 2-LEGGED OAUTH CREATING DOCUMENT *****/

/**** START EXAMPLE OF 2-LEGGED OAUTH PULLING CONTACTS

$myOauthRequest = new OAuth1Request(HTTPConstants::METHOD_GET, 'https://www.google.com/m8/feeds/contacts/default/full/?xoauth_requestor_id=administrator@apps-email.info&max-results=10', OAuth1Request::OAUTH_AUTHENTICATION_METHOD_HEADER);
$myOauthRequest->constructOauthRequest();

$myrequest = new HTTPGetRequest();
$myrequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
$myrequest->setConnectionHost("www.google.com");
$myrequest->setHttpHost("www.google.com");
$myrequest->setPath("m8/feeds/contacts/default/full/?xoauth_requestor_id=administrator@apps-email.info&max-results=10");

$myrequest->addHeader(new HTTPHeader('Authorization', $myOauthRequest->getFinalOauthAuthorizationHeader()));
$myrequest->executeRequest();


//echo $myrequest->getHttpResponse()->getResponseStatusCode() . "<hr><br /><br />";
//print_r($myrequest->getHttpResponse()->getResponseHeaders()) . "<hr><br /><br />";
echo $myrequest->getHttpResponse()->getResponseContent() . "<hr><br /><br />";

ENX EXAMPLE OF 2-LEGGED OAUTH PULLING CONTACTS ****/

/**** START EXAMPLE OF 2-LEGGED OAUTH PULLING LABELS
$myOauthRequest = new OAuth1Request(HTTPConstants::METHOD_GET, 'https://apps-apis.google.com/a/feeds/emailsettings/2.0/apps-email.info/test1/label?xoauth_requestor_id=test1@apps-email.info', OAuth1Request::OAUTH_AUTHENTICATION_METHOD_HEADER);
$myOauthRequest->constructOauthRequest();

$myrequest = new HTTPGetRequest();
$myrequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
$myrequest->setConnectionHost("apps-apis.google.com");
$myrequest->setHttpHost("apps-apis.google.com");
$myrequest->setPath("a/feeds/emailsettings/2.0/apps-email.info/test1/label?xoauth_requestor_id=test1@apps-email.info");

$myrequest->addHeader(new HTTPHeader('Authorization', $myOauthRequest->getFinalOauthAuthorizationHeader()));
$myrequest->executeRequest();


//echo $myrequest->getHttpResponse()->getResponseStatusCode() . "<hr><br /><br />";
//print_r($myrequest->getHttpResponse()->getResponseHeaders()) . "<hr><br /><br />";
echo $myrequest->getHttpResponse()->getResponseContent() . "<hr><br /><br />";

**** END EXAMPLE OF 2-LEGGED OAUTH PULLING LABELS */

/**** START EXAMPLE OF 2-LEGGED OAUTH CREATING LABELS

$myOauthRequest = new OAuth1Request(HTTPConstants::METHOD_POST, 'https://apps-apis.google.com/a/feeds/emailsettings/2.0/apps-email.info/test1/label?xoauth_requestor_id=test1@apps-email.info', OAuth1Request::OAUTH_AUTHENTICATION_METHOD_HEADER);
$myOauthRequest->constructOauthRequest();

$myrequest = new HTTPPostRequest();
$myrequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
$myrequest->setConnectionHost("apps-apis.google.com");
$myrequest->setHttpHost("apps-apis.google.com");
$myrequest->setPath("a/feeds/emailsettings/2.0/apps-email.info/test1/label?xoauth_requestor_id=test1@apps-email.info");
$myrequest->setPostData('<?xml version="1.0" encoding="utf-8"?>
<atom:entry xmlns:atom="http://www.w3.org/2005/Atom" xmlns:apps="http://schemas.google.com/apps/2006">
    <apps:property name="label" value="new-label" />
</atom:entry>');
$myrequest->addHeader(new HTTPHeader('Content-Type', 'application/atom+xml'));
$myrequest->addHeader(new HTTPHeader('Authorization', $myOauthRequest->getFinalOauthAuthorizationHeader()));
$myrequest->executeRequest();


echo $myrequest->getHttpResponse()->getResponseStatusCode() . "<hr><br /><br />";
print_r($myrequest->getHttpResponse()->getResponseHeaders()) . "<hr><br /><br />";
echo $myrequest->getHttpResponse()->getResponseContent() . "<hr><br /><br />";

****/

?>
