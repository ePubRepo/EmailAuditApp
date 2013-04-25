<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class HTTPGetRequest extends HTTPRequest
{
	function __construct()
	{
		parent::setMethod(HTTPConstants::METHOD_GET);
	}
	
	/*
	 * Executed HTTP GET request and stores the response as a HTTPResponse object.
	 */
	public function executeRequest()
	{
		$prefix = ($this->protocol == HTTPConstants::PROTOCOL_HTTPS) ? (HTTPConstants::PROTOCOL_HTTPS) : ("");
		$fp = fsockopen($prefix . $this->connectionHost, $this->port, $errno, $errstr, 15);

		Logger::add_info_log_entry(__FILE__ . __LINE__ . " HTTP GET Request Socket: " . $prefix . $this->connectionHost . $this->port);
		
		if ($fp)
		{
			$localquerystring = "";
			if (strlen($this->querystring) > 0)
			{
				$localquerystring = "?" . $this->querystring;
			}
		
			// send the request headers:
			fputs($fp, $this->method . " /" . $this->path . $localquerystring . " HTTP/1.1\r\n");
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " GET Header: " . $this->method . " /" . $this->path . $localquerystring . " HTTP/1.1");
			
			fputs($fp, "Host: " . $this->httpHost . "\r\n");
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " GET Header: Host: " . $this->httpHost);
			
			foreach ($this->headers as $header)
			{
				fputs($fp, $header->getHeader() . ": " . $header->getValue()  . "\r\n");
				Logger::add_info_log_entry(__FILE__ . __LINE__ . " GET Header: " . $header->getHeader() . ": " . $header->getValue());
			}
			
			fputs($fp, "Connection: close\r\n");
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " GET Header: Connection: close");
		
			//Should be end of headers in GET request
			fputs($fp, "\r\n");
		
			$result = '';
			while(!feof($fp))
			{
				// receive the results of the request
				$result .= fgets($fp, 128);
		    }
		}
		else
		{ 
			Logger::add_error_log_entry(__FILE__ . __LINE__ . " Error Establishing HTTP GET Request Socket With: " . $prefix . $this->connectionHost . $this->port . '; ' . $errno . ' ' . $errstr);
			throw new HTTPConnectionException("Error in HTTP Connection. " . $errorno . "; " . $errorstr);
		}
		
		// close the socket connection
		fclose($fp);
		
		$this->httpresponse = new HTTPResponse($result);
	}
}

?>
