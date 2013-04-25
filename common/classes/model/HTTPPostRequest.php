<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class HTTPPostRequest extends HTTPRequest
{
	private $postData;
	
	function __construct()
	{
		parent::setMethod(HTTPConstants::METHOD_POST);
	}
	
	/*
	 * Add values to be passed as a part of the data/content of the POST request
	 * 
	 * @param $key The variable name to be appended to the HTTP Post request's data section
	 * @param $value The variable value to be appended to the HTTP Post request's data section
	 */
	public function addPostKeyValueDataPair($key, $value)
	{
		if (strlen($this->postData) == 0)
		{
			$this->postData .= $key . "=" . $value;
		}
		else
		{
			$this->postData .= "&" . $key . "=" . $value;
		}
	}
	
	/*
	 * Set the literal raw data value of the POST request
	 * 
	 * @param $data The raw literal value/content of the POST request
	 */
	public function setPostData($data)
	{
		$this->postData = $data;
	}
	
	/*
	 * Executed HTTP POST request and stores the response as a HTTPResponse object.
	 */
	public function executeRequest()
	{
		$prefix = ($this->protocol == HTTPConstants::PROTOCOL_HTTPS) ? (HTTPConstants::PROTOCOL_HTTPS) : ("");
		$fp = fsockopen($prefix . $this->connectionHost, $this->port, $errno, $errstr, 15);

		if ($fp)
		{
			$localquerystring = "";
			if (strlen($this->querystring) > 0)
			{
				$localquerystring = "?" . $this->querystring;
			}
			
			// send the request headers:
			fputs($fp, $this->method . " /" . $this->path . $localquerystring . " HTTP/1.1\r\n");
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " POST Header: " . $this->method . " /" . $this->path . $localquerystring . " HTTP/1.1");
			
			fputs($fp, "Host: " . $this->httpHost . "\r\n");
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " POST Header: Host: " . $this->httpHost);
			
			fputs($fp, "Connection: close\r\n");
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " POST Header: Connection: close");
			
			foreach ($this->headers as $header)
			{
				fputs($fp, $header->getHeader() . ": " . $header->getValue()  . "\r\n");
				Logger::add_info_log_entry(__FILE__ . __LINE__ . " POST Header: " . $header->getHeader() . ": " . $header->getValue());
			}
			fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " POST Header: Content-Type: application/x-www-form-urlencoded");
			
			fputs($fp, "Content-Length: ". strlen($this->postData) ."\r\n");
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " POST Header: Content-Length: " . strlen($this->postData));

			// second line break above data feed
			fputs($fp, "\r\n");
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " POST Header: ");
			
			fputs($fp, $this->postData . "\r\n");
			Logger::add_info_log_entry(__FILE__ . __LINE__ . " POST Data: " . $this->postData);

			$result = '';
			while(!feof($fp))
			{
				// receive the results of the request
				$result .= fgets($fp, 128);
		    }
		}
		else
		{ 
			throw new HTTPConnectionException("Error in HTTP Connection. " . $errorno . "; " . $errorstr);
		}
		
		// close the socket connection
		fclose($fp);
		
		$this->httpresponse = new HTTPResponse($result);
	}
}

?>
