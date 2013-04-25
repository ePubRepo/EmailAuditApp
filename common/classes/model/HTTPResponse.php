<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class HTTPResponse
{
	private $http_response_raw;
	
	/*
	 * Store array of HTTPHeader objects associated with each HTTP response header
	 */
	private $http_response_headers = array();
	private $http_response_content;
	private $http_response_status_code;
	
	/*
	 * @param $raw_response String value containing the raw socket-level HTTP response from the HTTP request
	 *    Thsi will contain the HTTP response headers and the HTTP body content together in the same string
	 */
	function __construct($raw_response)
	{
		$this->http_response_raw = $raw_response;
		
		$this->parseSetResponseHeaders();
		$this->parseSetResponseStatusCode();
		$this->parseSetResponseContent();
	}
	
	private function parseSetResponseStatusCode()
	{
		$result = explode("\r\n\r\n", $this->http_response_raw, 2);
 		$headers_raw_str = isset($result[0]) ? $result[0] : '';
 		
		preg_match('/^HTTP\/1.1 ([0-9]{3}) /', $headers_raw_str, $status_code);
		$this->http_response_status_code = trim($status_code[1]);
		
		Logger::add_info_log_entry(__FILE__ . __LINE__ . " HTTP Response Status Code: " . $this->http_response_status_code);
	}

	private function parseSetResponseHeaders()
	{
		$result = explode("\r\n\r\n", $this->http_response_raw, 2);
 		$headers_raw_str = isset($result[0]) ? $result[0] : '';
 		
 		Logger::add_info_log_entry(__FILE__ . __LINE__ . " HTTP Response Headers: " . $headers_raw_str);
 		
 		$headers_raw_array = explode("\r\n", $headers_raw_str);
 		
 		//remove 0 element, which is "HTTP/1.1 200 OK" and is not a header but rather a status code
 		array_shift($headers_raw_array);
 		foreach($headers_raw_array as $raw_header)
 		{
 			$split = strpos($raw_header, ':');
 			$header = trim(substr($raw_header, 0, $split));
 			$value = trim(substr($raw_header, $split+1));
 			array_push($this->http_response_headers, new HTTPHeader($header, $value));
 		}
	}
	
	/*
	 * Take the raw HTTP text response from the socket, parse it, and save the response content
	 */
	private function parseSetResponseContent()
	{
		$result = explode("\r\n\r\n", $this->http_response_raw, 2);
 		$this->http_response_content = isset($result[1]) ? $result[1] : '';
 		
 		if (strlen($this->http_response_content) < 7000)
 		{
 			Logger::add_info_log_entry(__FILE__ . __LINE__ . " HTTP Response Content: " . $this->http_response_content);
 		}
	}
	
	/*
	 * @return String value containing the raw socket-level text response from the HTTP request
	 * 	This will include the HTTP response headers and the HTTP response content
	 */
	public function getRawResponse()
	{
		return $this->http_response_raw;
	}
	
	/*
	 * @return Array of HTTPHeaders corresponding to the HTTP response headers from the HTTP request
	 */
	public function getResponseHeaders()
	{
		return $this->http_response_headers;
	}
	
	/*
	 * @return String value containing the raw HTTP body content (i.e., the page content and not the headers)
	 * 	from the HTTP request
	 */
	public function getResponseContent()
	{
		return $this->http_response_content;
	}
	
	/*
	 * @return Int value of the HTTP response status code (e.g., 200, 404, 401)
	 */
	public function getResponseStatusCode()
	{
		return $this->http_response_status_code;
	}
}

?>
