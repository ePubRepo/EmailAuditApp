<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class MailboxExportStatusList
{
	private $arrMailboxExportStatus = array();
	
	public function addMailboxExportStatus(MailboxExportStatus $status)
	{
		//TODO: check for dupes before adding a status based upon date+username or at least log dupes
		array_push($this->arrMailboxExportStatus, $status);
	}
	
	public function getArrayOfMailboxExportStatuses()
	{
		return $this->arrMailboxExportStatus;
	}
}

?>
