<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

/*

Raw GET variables from openid response

Array
(
    [openid_ns] => http://specs.openid.net/auth/2.0
    [openid_mode] => id_res
    [openid_op_endpoint] => https://www.google.com/accounts/o8/ud
    [openid_response_nonce] => 2011-09-26T03:25:23ZbpEcxJKw4XnBrQ
    [openid_return_to] => https://www.apps-apps.info/emailarchive/identity_check_response.php
    [openid_assoc_handle] => AOQobUdWt36hjUZfCgcgeLVm3hi9K5VWkMjIMd5zEJeA1lxiIF5fFXEJ
    [openid_signed] => op_endpoint,claimed_id,identity,return_to,response_nonce,assoc_handle,ns.ext1,ext1.mode,ext1.type.firstname,ext1.value.firstname,ext1.type.email,ext1.value.email,ext1.type.lastname,ext1.value.lastname
    [openid_sig] => zvB3pQrHJ4HpU+Otm7vTIK9NhVE=
    [openid_identity] => https://www.google.com/accounts/o8/id?id=AItOawn6MN02uIKi30uQYAJ_auhCjepuugTtI-Q
    [openid_claimed_id] => https://www.google.com/accounts/o8/id?id=AItOawn6MN02uIKi30uQYAJ_auhCjepuugTtI-Q
    [openid_ns_ext1] => http://openid.net/srv/ax/1.0
    [openid_ext1_mode] => fetch_response
    [openid_ext1_type_firstname] => http://axschema.org/namePerson/first
    [openid_ext1_value_firstname] => Test
    [openid_ext1_type_email] => http://axschema.org/contact/email
    [openid_ext1_value_email] => administrator@mypremierapps.info
    [openid_ext1_type_lastname] => http://axschema.org/namePerson/last
    [openid_ext1_value_lastname] => User
) 

***OR***

Array
(
    [openid_mode] => cancel
    [openid_ns] => http://specs.openid.net/auth/2.0
)


 */

class IdentityCheckResponse extends OpenIdResponse
{
	function __construct($getVariables)
	{
		parent::__construct($getVariables, array());
	}
}

?>
