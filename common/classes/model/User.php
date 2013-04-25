<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class User
{
	private $emailAddress;
	private $lastName;
	private $firstName;
	
	function __construct($emailAddress)
	{
		$this->emailAddress = $emailAddress;
	}
	
	public function getEmailAddress()
	{
		return $this->emailAddress;
	}
	
	public function setFirstname($firstname)
	{
		$this->firstName = $firstname;
	}

	public function getFirstname()
	{
		return $this->firstName;
	}
	
	public function setLastname($lastname)
	{
		$this->lastName = $lastname;
	}
	
	public function getLastname()
	{
		return $this->lastName;
	}
}

?>
