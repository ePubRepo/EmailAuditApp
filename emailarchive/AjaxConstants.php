<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

abstract class AjaxConstants {
	/**
	 * The name of a JSON variable to be sent back with every AJAX request in the
	 * Email Archive App so that the browser can be sure that the response it is receiving is a valid
	 * AJAX response (i.e., not a stack trace, server error, etc).
	 * 
	 * The script still may have failed (i.e., Google API called failed), but the AJAX call to the Email Archive App
	 * itself succeeded. We cannot depend upon a 200 as PHP can throw back 200 with a PHP stack trace, so we need
	 * another means to make sure that at least the PHP script executed fully and successfully.
	 */
	const RESPONSE_PROCESS_STATUS_VARIABLE = 'response_status';
	
	/**
	 * This variable is an encapsulation variable that holds the contents of the AJAX response data
	 * This is necessary as there is also a response_status variable
	 */
	const RESPONSE_CONTENTS_VARIABLE = 'response_contents';
	
	const RESPONSE_PROCESSED_SUCCESSFULLY = 10;
	const RESPONSE_PROCESSED_AUTHORIZATION_ERROR = 9;
	const RESPONSE_PROCESSED_UNCAUGHT_ERROR = 8;
	const RESPONSE_PROCESSED_DOMAIN_CANNOT_USE_API = 7;
	const RESPONSE_PROCESSED_USER_NOT_AUTHORIZED_API = 6;
	const RESPONSE_PROCESSED_NO_OAUTH_TOKEN_REPOSITORY = 5;
	const RESPONSE_PROCESSED_INVALID_USER = 4;
}
?>
