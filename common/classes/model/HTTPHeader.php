<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class HTTPHeader
{
	private $header;
	private $value;
	
	/*
	 * @param $header String value containing the name of the HTTP header (e.g., "Content-Type", "User-Agent")
	 * @param $value String value containing the value of the HTTP header (e.g., "text/html", "Mozilla Firefox")
	 */
	function __construct($header, $value)
	{
		$this->header = $header;
		$this->value = $value;
	}
	public function getHeader()
	{
		return $this->header;
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
}

?>
