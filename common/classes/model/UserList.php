<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class UserList
{
	private $arrOfUsers = array();
	
	/*
	 * Function is write-safe and will not add a new user object when one is already in the array for the user
	 */
	public function addUser(User $user)
	{
		$str_user_repository = serialize($this->arrOfUsers);
		if (strpos($str_user_repository, $user->getEmailAddress()) !== false)
			return;
		
		array_push($this->arrOfUsers, $user);
	}

	public function getUser($email)
	{
		foreach ($this->arrOfUsers as $user)
		{
			if ($user->getEmailAddress() == $email)
			{
				return $user;
			}
		}
	}
	
	public function getArrayOfUsers()
	{
		return $this->arrOfUsers;
	}
}

?>
