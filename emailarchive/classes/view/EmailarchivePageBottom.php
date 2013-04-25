<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class EmailarchivePageBottom
{
	public function getPublicHtml()
	{
		$html = Printer::appendNewLineCharacters('<div class="bottom-container">');
		$html .= Printer::appendNewLineCharacters('<div class="center-container">');
		$html .= Printer::appendNewLineCharacters('<div class="bottom-container-links"><a href="terms_privacy.php">Terms &amp; Privacy</a> &middot; <a href="help.php">Help</a> &middot; <a href="about.php">About</a></div>');
		$html .= Printer::appendNewLineCharacters('</div>');
		$html .= Printer::appendNewLineCharacters('</div>');
		
		// Close #page-contents-container
		$html .= Printer::appendNewLineCharacters('</div>');
		
		return $html;
	}

}
?>
