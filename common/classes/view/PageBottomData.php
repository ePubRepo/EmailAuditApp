<?php

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