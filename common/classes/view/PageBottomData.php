<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class PageBottomData
{
	public function getPublicHtml()
	{
		$html = Printer::appendNewLineCharacters('</body>');	
		$html .= Printer::appendNewLineCharacters('</html>');
		return $html;
	}
}

?>
