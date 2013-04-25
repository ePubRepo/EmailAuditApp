<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

abstract class HTTPRequest
{
	protected $prtocol;
	protected $connectionHost;
	protected $httpHost;
	protected $port;
	protected $method;
	protected $path;
	protected $querystring;
	
	protected $headers = array();
	
	protected $httpresponse;
	
	public function setProtocol($protocol)
	{
		$this->protocol = $protocol;
		if ($this->protocol == HTTPConstants::PROTOCOL_HTTP)
		{
			$this->port = HTTPConstants::PORT_HTTP;
		}
		else if ($this->protocol == HTTPConstants::PROTOCOL_HTTPS)
		{
			$this->port = HTTPConstants::PORT_HTTPS;	
		}
	}
	
	/*
	 * The host with which to establsih a socket-level connection
	 * 
	 * @param $connectionHost The socket-level server connection host
	 */
	public function setConnectionHost($connectionHost)
	{
		$this->connectionHost = $connectionHost;
	}

	/*
	 * Set the value of the "Host: [host.com]" HTTP request parameter
	 * 
	 * @param $httpHost The HTTP request Host value
	 */
	public function setHttpHost($httpHost)
	{
		$this->httpHost = $httpHost;
	}
	
	public function setPort($port)
	{
		$this->port = $port;
	}
	
	protected function setMethod($method)
	{
		$this->method = $method;
	}

	public function setPath($path)
	{
		//check for preceeding "/" which should not be there as a "/" is set later and will otherwsie result in "//"
		if (substr($path, 0, 1) == "/")
		{
			$this->path = substr($path, 1);
		}
		else
		{
			$this->path = $path;
		}
	}

	public function setQuerystring($querystring)
	{
		$this->path = $querystring;
	}
	
	/*
	 * @param $inputHeader HTTPHeader object to be appended as a part of the HTTP headers to the HTTP request
	 */
	public function addHeader(HTTPHeader $inputHeader)
	{
		array_push($this->headers, $inputHeader);
	}
	
	/*
	 * @return HTTPResponse An HTTP Response object that resulted from the executed HTTP request
	 */
	public function getHttpResponse()
	{
		return $this->httpresponse;
	}
	
	abstract public function executeRequest();
}

?>
