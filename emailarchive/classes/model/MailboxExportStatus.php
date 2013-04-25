<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class MailboxExportStatus
{
	/*
The values for the current status are:
PENDING
    The request is being processed. 
ERROR
    The request failed due to some error. 
COMPLETED
    The request has been processed completely and the encrypted account information files are ready for download. 
MARKED_DELETE
    The request is marked for deletion next time the Google cleanup job runs. See Deleting the requested summary of a user account's information. 
DELETED
    The account summary information files were successfully deleted by the admin using Deleting the requested summary of a user account's information operation. 
EXPIRED
    The account information files were deleted by Google after the 3 week retention limit.     
	 */
	private $status;	

	// the url containing the mbox formatted file
	private $fileUrl;
	
	// numerical id corresponding with the email export request
	private $requestId;
	
	// email address associated with administrator account requesting the export of the end-user
	private $adminEmailAddress;
	
	// date the export request was completed
	private $completedDate;

	// date the export request expired
	private $expiredDate;
	
	// date the export request was generated
	private $requestDate;
	
	// end user email address (i.e., address of account we are exporting)
	private $endUserEmailAddress;
	
	public function setStatus($input)
	{
		$this->status = $input;
	}
	
	public function getStatus()
	{
		return $this->status;
	}
	
	public function setFileUrl($input)
	{
		$this->fileUrl = $input;
	}
	
	public function getFileUrl()
	{
		return $this->fileUrl;	
	}
	
	public function setRequestId($input)
	{
		$this->requestId = $input;
	}
	
	public function getRequestId()
	{
		return $this->requestId;
	}
	
	public function setAdminEmailAddress($input)
	{
		$this->adminEmailAddress = $input;
	}
	
	public function getAdminEmailAddress()
	{
		return $this->adminEmailAddress;
	}
	
	public function setCompletedDate($input)
	{
		$this->completedDate = $input;
	}
	
	public function getCompletedDate()
	{
		return $this->completedDate;
	}
	
	public function setRequestDate($input)
	{
		$this->requestDate = $input;
	}
	
	public function getRequestDate()
	{
		return $this->requestDate;
	}

	public function setExpiredDate($input)
	{
		$this->expiredDate = $input;
	}
	
	public function getExpiredDate()
	{
		return $this->expiredDate;
	}
	
	public function setUserEmailAddress($input)
	{
		$this->endUserEmailAddress = $input;
	}
	
	public function getUserEmailAddress()
	{
		return $this->endUserEmailAddress;
	}
}

?>
