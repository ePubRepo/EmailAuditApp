<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

abstract class OpenIdConstants
{
	const OPENID_ENDPOINT_DISCOVERY_URL = 'https://www.google.com/accounts/o8/id';
	const OPENID_NS = 'http://specs.openid.net/auth/2.0';
	const OPENID_CLAIMED_ID = 'http://specs.openid.net/auth/2.0/identifier_select';
	const OPENID_IDENTITY = 'http://specs.openid.net/auth/2.0/identifier_select';
	const OPENID_NS_AX = 'http://openid.net/srv/ax/1.0';
	const OPENID_AX_MODE_FETCH = 'fetch_request';
	const OPENID_NS_OAUTH = 'http://specs.openid.net/extensions/oauth/1.0';
	
	const OPENID_MODE_SETUP = 'checkid_setup';
	const OPENID_MODE_IMMEDIATE = 'checkid_setup';
	
	const OPENID_UI_MODE_SESSION = 'x-has-session';

	const OPENID_REALM = 'https://www.apps-apps.info';
	
	public function getOpenIdDiscoveryUrl()
	{
		return self::OPENID_ENDPOINT_DISCOVERY_URL;
	}
}

?>
