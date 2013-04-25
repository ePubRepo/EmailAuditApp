<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

/*
 * Example Response
Array
(
    [openid_ns] => http://specs.openid.net/auth/2.0
    [openid_mode] => id_res
    [openid_op_endpoint] => https://www.google.com/accounts/o8/ud
    [openid_response_nonce] => 2011-08-14T18:32:16Z_K6OJS_-J3hCSg
    [openid_return_to] => http://www.apps-email.info/apps/audit/v2/callback.php
    [openid_assoc_handle] => AOQobUdR54m3T1FjteZ-c0JGS6cQBxVZjWXS383Y6nAqCyO-FDL6lY7c
    [openid_signed] => op_endpoint,claimed_id,identity,return_to,response_nonce,assoc_handle,ns.ext1,ns.ext2,ext1.mode,ext1.type.firstname,ext1.value.firstname,ext1.type.email,ext1.value.email,ext1.type.lastname,ext1.value.lastname,ext2.scope,ext2.request_token
    [openid_sig] => MwxE4z3DcOaLFqjEf2k0w023Ra4=
    [openid_identity] => https://www.google.com/accounts/o8/id?id=AItOawl2Du_JsvUkpXbUXgTlKwt7DeNvVuhXfzo
    [openid_claimed_id] => https://www.google.com/accounts/o8/id?id=AItOawl2Du_JsvUkpXbUXgTlKwt7DeNvVuhXfzo
    [openid_ns_ext1] => http://openid.net/srv/ax/1.0
    [openid_ext1_mode] => fetch_response
    [openid_ext1_type_firstname] => http://axschema.org/namePerson/first
    [openid_ext1_value_firstname] => eric
    [openid_ext1_type_email] => http://axschema.org/contact/email
    [openid_ext1_value_email] => eric@apps-email.info
    [openid_ext1_type_lastname] => http://axschema.org/namePerson/last
    [openid_ext1_value_lastname] => eric
    [openid_ns_ext2] => http://specs.openid.net/extensions/oauth/1.0
    [openid_ext2_scope] => http://docs.google.com/feeds/ http://spreadsheets.google.com/feeds/ https://www.googleapis.com/auth/apps/reporting/audit.readonly
    [openid_ext2_request_token] => 4/GP1PRon_sXWCykdHihvpsq78wm6p
)
*/

/**
Example Valid Response 
 
final_landing_url:/emailarchive/
openid.ns:http://specs.openid.net/auth/2.0
openid.mode:id_res
openid.op_endpoint:https://www.google.com/accounts/o8/ud
openid.response_nonce:2012-01-25T01:46:53Za41Gqh0NldeQlw
openid.return_to:https://www.apps-apps.info/identity_check_response.php?final_landing_url=/emailarchive/
openid.assoc_handle:AMlYA9W7wR-hi31K2YJ_0ShZLFCROUm1fEqZxhFp6Jrce09i3SRjs4cG
openid.signed:op_endpoint,claimed_id,identity,return_to,response_nonce,assoc_handle,ns.ext1,ext1.mode,ext1.type.firstname,ext1.value.firstname,ext1.type.email,ext1.value.email,ext1.type.lastname,ext1.value.lastname
openid.sig:NtXRLKtN9zKZp1dteTlOjMsWCY0=
openid.identity:https://www.google.com/accounts/o8/id?id=AItOawlHi8mnA2RiLIUVd019khguWFgjkikSWVc
openid.claimed_id:https://www.google.com/accounts/o8/id?id=AItOawlHi8mnA2RiLIUVd019khguWFgjkikSWVc
openid.ns.ext1:http://openid.net/srv/ax/1.0
openid.ext1.mode:fetch_response
openid.ext1.type.firstname:http://axschema.org/namePerson/first
openid.ext1.value.firstname:John
openid.ext1.type.email:http://axschema.org/contact/email
openid.ext1.value.email:administrator@apps-email.info
openid.ext1.type.lastname:http://axschema.org/namePerson/last
openid.ext1.value.lastname:Doe
 */

//TODO: SECURE INPUTS

/**
Examle Successful/Valid Responses

xrequested_scopes:https://apps-apis.google.com/a/feeds/compliance/audit/+https://apps-apis.google.com/a/feeds/user/
openid.ns:http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0
openid.mode:id_res
openid.op_endpoint:https%3A%2F%2Fwww.google.com%2Faccounts%2Fo8%2Fud
openid.response_nonce:2012-01-25T01%3A29%3A36ZnmrBOdmTsn7DEw
openid.return_to:https%3A%2F%2Fwww.apps-apps.info%2Femailarchive%2Fopenid_oauth_callback.php%3F%26xrequested_scopes%3Dhttps%3A%2F%2Fapps-apis.google.com%2Fa%2Ffeeds%2Fcompliance%2Faudit%2F%2Bhttps%3A%2F%2Fapps-apis.google.com%2Fa%2Ffeeds%2Fuser%2F
openid.assoc_handle:AMlYA9WVTnqIKoqYso7F3IuoKrQ9QUg0AJlHsZCwRUmVziiEVB3EmkS6
openid.signed:op_endpoint%2Cclaimed_id%2Cidentity%2Creturn_to%2Cresponse_nonce%2Cassoc_handle%2Cns.ext1%2Cns.ext2%2Cext1.mode%2Cext1.type.firstname%2Cext1.value.firstname%2Cext1.type.email%2Cext1.value.email%2Cext1.type.lastname%2Cext1.value.lastname%2Cext2.scope%2Cext2.request_token
openid.sig:8V1UoMUmmHop%2BawFXhjbBIOpbwc%3D
openid.identity:https%3A%2F%2Fwww.google.com%2Faccounts%2Fo8%2Fid%3Fid%3DAItOawlHi8mnA2RiLIUVd019khguWFgjkikSWVc
openid.claimed_id:https%3A%2F%2Fwww.google.com%2Faccounts%2Fo8%2Fid%3Fid%3DAItOawlHi8mnA2RiLIUVd019khguWFgjkikSWVc
openid.ns.ext1:http%3A%2F%2Fopenid.net%2Fsrv%2Fax%2F1.0
openid.ext1.mode:fetch_response
openid.ext1.type.firstname:http%3A%2F%2Faxschema.org%2FnamePerson%2Ffirst
openid.ext1.value.firstname:John
openid.ext1.type.email:http%3A%2F%2Faxschema.org%2Fcontact%2Femail
openid.ext1.value.email:administrator%40apps-email.info
openid.ext1.type.lastname:http%3A%2F%2Faxschema.org%2FnamePerson%2Flast
openid.ext1.value.lastname:Doe
openid.ns.ext2:http%3A%2F%2Fspecs.openid.net%2Fextensions%2Foauth%2F1.0
openid.ext2.scope:https%3A%2F%2Fapps-apis.google.com%2Fa%2Ffeeds%2Fcompliance%2Faudit%2F+https%3A%2F%2Fapps-apis.google.com%2Fa%2Ffeeds%2Fuser%2F
openid.ext2.request_token:4%2FB1vkrnCWvn0EY94ozgM2CCpHAQB8
 */

//TODO: SECURE INPUTS

/*
 * documentation on openid response at http://code.google.com/apis/accounts/docs/OpenID.html#Response 
 */
class OpenIdResponse
{
	private $arrOpenIdResponseVariables;	
	
	private $arrGetVariables;
	private $arrPostVariables;

	function __construct($getVariables, $postVariables)
	{
		$this->arrGetVariables = (array) $getVariables;
		$this->arrPostVariables = (array) $postVariables;
		
		$this->parseResponse();
	}
	
	private function parseResponse()
	{
		if (isset($this->arrGetVariables)
			&& is_array($this->arrGetVariables))
		{
			foreach($this->arrGetVariables as $key => $value)
			{
				$this->arrOpenIdResponseVariables[$key] = $value;
			}
		}
		
		if (isset($this->arrPostVariables)
			&& is_array($this->arrPostVariables))
		{
			foreach($this->arrPostVariables as $key => $value)
			{
				$this->arrOpenIdResponseVariables[$key] = $value;
			}
		}
	}
	
	/*
	 * If the OpenId+OAuth flow has been followed properly with my classes, the response URL will contain the "xrequested_scopes"
	 * E.g.: http://www.apps-email.info/apps/email/openid_oauth_callback.php?xrequested_scopes=https://apps-apis.google.com/a/feeds/compliance/audit/+https://apps-apis.google.com/a/feeds/user/&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0 
	 * 
	 * @return Array array containing values of OAuth scopes authorized during this OAuth+OpenID session
	 */
	public function getOpenIdRequestedOAuthScopes()
	{
		if (isset($this->arrGetVariables['xrequested_scopes']))
		{
			//sample response variable: "https://apps-apis.google.com/a/feeds/compliance/audit/ https://apps-apis.google.com/a/feeds/user/"
			return explode(" ", $this->arrGetVariables['xrequested_scopes']);
		}
		else
		{
			return array();
		}
	}
	
	public function getOpenIdResponseVariable($variableName)
	{
		if (isset($this->arrOpenIdResponseVariables[$variableName]))
		{
			$var = urldecode($this->arrOpenIdResponseVariables[$variableName]);	
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " Requested Variable Name: " . $variableName . " Response: " . $var);
			return $var;
		}
		else
		{
			return null;
		}
	}
}

?>
