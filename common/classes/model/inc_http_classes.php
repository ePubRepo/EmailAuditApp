<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('HTTPConstants.php');
require_once('HTTPConnectionException.php');
require_once('HTTPRequest.php');
require_once('HTTPGetRequest.php');
require_once('HTTPPostRequest.php');
require_once('HTTPResponse.php');
require_once('HTTPHeader.php');


/**** START EXAMPLE OF CLIENT LOGIN WORKING 

$myrequest = new HTTPPostRequest();
$myrequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
$myrequest->setConnectionHost("www.google.com");
$myrequest->setHttpHost("www.google.com");
$myrequest->setPath("accounts/ClientLogin");
$myrequest->addPostKeyValueDataPair("accountType", "HOSTED");
$myrequest->addPostKeyValueDataPair("Email", "administrator@apps-email.info");
$myrequest->addPostKeyValueDataPair("Passwd", "abcabcabc");
$myrequest->addPostKeyValueDataPair("service", "apps");
$myrequest->addPostKeyValueDataPair("source", "appsemail-1");
$myrequest->executeRequest();

END EXAMPLE OF CLIENT LOGIN WORKING *****/


/**** START EXAMPLE OF CREATE LABEL WITH CLIENT LOGIN

$myrequest = new HTTPPostRequest();
$myrequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
$myrequest->setConnectionHost("apps-apis.google.com");
$myrequest->setHttpHost("apps-apis.google.com");
$myrequest->setPath("a/feeds/emailsettings/2.0/apps-email.info/administrator/label");
$myrequest->setPostData('<?xml version="1.0" encoding="utf-8"?>
<atom:entry xmlns:atom="http://www.w3.org/2005/Atom" xmlns:apps="http://schemas.google.com/apps/2006">
    <apps:property name="label" value="status updates" />
</atom:entry>');
$myrequest->addHeader(new HTTPHeader('Content-Type', 'application/atom+xml'));
$myrequest->addHeader(new HTTPHeader('Authorization', 'GoogleLogin auth=DQAAAPkAAAAJXDSy6TJofzYdqHiYZYuhaiHvhZ5VakT9X6sHDxmlXxAuRUFTH-cGvDFiChcbv4Pjl4n8bWtkmAqRKA3Mv-ISyN-Vcxj6aWGzwe65A-mOIPm7vfxkcZn6zgaxIfa2Z4IRQxnsVFjfcEvUzqH1niSX_V8uxaAJTVSSamXkv6f_DPBnGmRKLzkwySSC9sy1-L-Uig7fnPrICMPi3x6VzuAYZyNA5XLun_KGOjidzWt6VoqTt3M6_PWqqzU--5yrCoutcNdkoL85R_GWGUUtrtTjDwQ4Ecu0mdSDQw6RYkpa5qe1WOmsSqvpth-ctMmGy1HZhbwMDT9C44HuDTxi96cg'));
$myrequest->executeRequest();

END EXAMPLE OF CREATE LABEL WITH CLIENT LOGIN ****/

/**** START EXAMLPE OF CREATE FILTER WITH CLIENT LOGIN

$myrequest = new HTTPPostRequest();
$myrequest->setProtocol(HTTPConstants::PROTOCOL_HTTPS);
$myrequest->setConnectionHost("apps-apis.google.com");
$myrequest->setHttpHost("apps-apis.google.com");
$myrequest->setPath("a/feeds/emailsettings/2.0/apps-email.info/administrator/filter");
$myrequest->setPostData('<?xml version="1.0" encoding="utf-8"?>
	<atom:entry xmlns:atom="http://www.w3.org/2005/Atom" xmlns:apps="http://schemas.google.com/apps/2006">
	    <apps:property name="from" value="ebeach@google.com" />
	    <apps:property name="to" value="administrator@apps-email.info" />
	    <apps:property name="label" value="Misc" />
	</atom:entry>');
$myrequest->addHeader(new HTTPHeader('Content-Type', 'application/atom+xml'));
$myrequest->addHeader(new HTTPHeader('Authorization', 'GoogleLogin auth=DQAAAPkAAAAJXDSy6TJofzYdqHiYZYuhaiHvhZ5VakT9X6sHDxmlXxAuRUFTH-cGvDFiChcbv4Pjl4n8bWtkmAqRKA3Mv-ISyN-Vcxj6aWGzwe65A-mOIPm7vfxkcZn6zgaxIfa2Z4IRQxnsVFjfcEvUzqH1niSX_V8uxaAJTVSSamXkv6f_DPBnGmRKLzkwySSC9sy1-L-Uig7fnPrICMPi3x6VzuAYZyNA5XLun_KGOjidzWt6VoqTt3M6_PWqqzU--5yrCoutcNdkoL85R_GWGUUtrtTjDwQ4Ecu0mdSDQw6RYkpa5qe1WOmsSqvpth-ctMmGy1HZhbwMDT9C44HuDTxi96cg'));
$myrequest->executeRequest();

END EXAMPLE OF CREATE FILTER WITH CLIENT LOGIN ****/

?>
