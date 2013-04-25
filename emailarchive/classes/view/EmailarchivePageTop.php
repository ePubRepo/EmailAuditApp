<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class EmailarchivePageTop
{
	public $username;
	
	public function setUsername($username)
	{
		$this->username = $username;
	}

	public function getPublicHtml()
	{
		$html = Printer::appendNewLineCharacters('<div id="page-contents-container">');
		
		$html .= Printer::appendNewLineCharacters('<div class="top-container" id="page-top-container">');
		
		$html .= Printer::appendNewLineCharacters('<div class="center-container">');
		
		if (isset($this->username))
		{
			$html .= Printer::appendNewLineCharacters('<div id="top-menu-username"><div><span>' . $this->username . '</span><span class="sign-out-arrow"  onclick="if (document.getElementById(\'sign-out-box\').className == \'sign-out-link-displayed\') { document.getElementById(\'sign-out-box\').className = \'sign-out-link-hidden\'; } else {document.getElementById(\'sign-out-box\').className = \'sign-out-link-displayed\';} "></span></div><div id="sign-out-box" class="sign-out-link-hidden"><span class="sign-out-link" onclick="window.location=\'welcome.php?mode=signout\'; return false;">Sign Out</span></div></div>');
		}
		
		$html .= Printer::appendNewLineCharacters('<div class="top-header">Email Archive App</div>');
		$html .= Printer::appendNewLineCharacters('</div>');
		
		$html .= Printer::appendNewLineCharacters('</div>');
		
		return $html;
	}

}

?>
