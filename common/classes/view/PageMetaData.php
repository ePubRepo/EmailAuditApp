<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class PageMetaData
{
	private $title = 'Email Archive App';
	private $bodyOnLoad;
	private $inlineJavaScript;
	private $javaScriptFiles = array();
	private $cssFiles = array();
	
	public function setTitle($title)
	{
		$this->title = $title;
	}
	
	public function setBodyOnLoad($onLoad)
	{
		$this->bodyOnLoad = $onLoad;
	}
	
	public function addJavaScript($script)
	{
		$this->inlineJavaScript = $script;
	}

	public function addJavaScriptFile($absolutePathFromWwwRoot, $async_load = false)
	{
		array_push($this->javaScriptFiles, array ($absolutePathFromWwwRoot, $async_load));
	}

	public function addCssFile($absolutePathFromWwwRoot)
	{
		array_push($this->cssFiles, $absolutePathFromWwwRoot);
	}
	
	public function getPublicHtml()
	{
		$html = Printer::appendNewLineCharacters('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
		$html .= Printer::appendNewLineCharacters('<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml">');
		$html .= Printer::appendNewLineCharacters('<head>');
		$html .= Printer::appendNewLineCharacters('<title>' . $this->title . '</title>');
		$html .= Printer::appendNewLineCharacters('<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />');
		
		if (count($this->cssFiles) > 0)
		{
			foreach ($this->cssFiles as $cssFile)
			{
				$html .= Printer::appendNewLineCharacters('<link rel="StyleSheet" href="' . $cssFile . '" type="text/css" />');
			}
		}
		
		if (count($this->javaScriptFiles) > 0)
		{
			foreach ($this->javaScriptFiles as $jsFileData)
			{
				if ($jsFileData[1] == true)
				{
					//async loading of JS file desired
					$html .= Printer::appendNewLineCharacters('<script type="text/javascript">');
					$html .= Printer::appendNewLineCharacters('(function() {');
					$html .= Printer::appendNewLineCharacters('   function async_load(){');
					$html .= Printer::appendNewLineCharacters('      var s = document.createElement(\'script\');');
					$html .= Printer::appendNewLineCharacters('      s.type = \'text/javascript\';');
					$html .= Printer::appendNewLineCharacters('      s.async = true;');
					$html .= Printer::appendNewLineCharacters('      s.src = \'' . $jsFileData[1] . '\';');
					$html .= Printer::appendNewLineCharacters('      var x = document.getElementsByTagName(\'script\')[0];');
					$html .= Printer::appendNewLineCharacters('      x.parentNode.insertBefore(s, x);');
					$html .= Printer::appendNewLineCharacters('   }');
					$html .= Printer::appendNewLineCharacters('   if (window.attachEvent)');
					$html .= Printer::appendNewLineCharacters('      window.attachEvent(\'onload\', async_load);');
					$html .= Printer::appendNewLineCharacters('   else');
					$html .= Printer::appendNewLineCharacters('      window.addEventListener(\'load\', async_load, false);');
					$html .= Printer::appendNewLineCharacters('})();');
					$html .= Printer::appendNewLineCharacters('</script>');
				}
				else
				{
					$html .= Printer::appendNewLineCharacters('<script src="' . $jsFileData[0] . '"></script>');
				}
			}
		}
		
		if (isset($this->inlineJavaScript))
		{
			$html .= Printer::appendNewLineCharacters('<script type="text/javascript">');
			$html .= Printer::appendNewLineCharacters($this->inlineJavaScript);
			$html .= Printer::appendNewLineCharacters('</script>');
		}
		
		$html .= Printer::appendNewLineCharacters('<meta name="Description" content="Free Google Apps Apps" />');
		$html .= Printer::appendNewLineCharacters('</head>');
		$html .= Printer::appendNewLineCharacters('<body' . (isset($this->bodyOnLoad) ? ' onload="' . $this->bodyOnLoad . '"' : '') . '>');
		return $html;
	}
}

?>
