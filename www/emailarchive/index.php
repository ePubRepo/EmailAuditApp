<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('../../emailarchive/inc_email_archive_bootstrap.php');

EmailArchiveAccessIdentityCheckController::checkAccessIdentityAndPerformRedirection();

$myPageMetaData = new PageMetaData();
$myPageMetaData->setTitle('Email Archive App');
$myPageMetaData->addCssFile(FileVersioner::getPathToFile('/emailarchive/css/general.css'));

if (DevelopmentModeController::showDevelopmentMode())
{
	//Development Mode; Serve Development JavaScript
	$myPageMetaData->addJavaScriptFile('/closure-library-2389asj2023/closure/goog/base.js');
	$myPageMetaData->addJavaScriptFile('/emailarchive/js/deps-2389asj2023.js');
	$myPageMetaData->addJavaScriptFile('/emailarchive/js/spin.js');
	
	$myPageMetaData->addJavaScript('goog.require(\'app.emailarchive.bootstrap\');');
}
else
{
	//Production Mode; Serve Production JavaScript
	$myPageMetaData->addJavaScriptFile(FileVersioner::getPathToFile('/emailarchive/js/prod/prod.js'));
	$myPageMetaData->addJavaScriptFile(FileVersioner::getPathToFile('/emailarchive/js/prod/spin.js'));
}

$myPageMetaData->setBodyOnLoad('new app.emailarchive.bootstrap();');

echo $myPageMetaData->getPublicHtml();

$myPageTopEmail = new EmailarchivePageTop();
if (strlen(AccessIdentityController::getPurportedEmailFromHttpRequest()) > 0)
{
	$myPageTopEmail->setUsername(AccessIdentityController::getPurportedEmailFromHttpRequest());
}
else
{
	//TODO: log error
}

echo $myPageTopEmail->getPublicHtml();
?>
<noscript>
<p>Apps on www.apps-apps.info require JavaScript enabled in your browser to function properly.</p> 
</noscript>
<?php

$myPageBottomEmail = new EmailarchivePageBottom();
echo $myPageBottomEmail->getPublicHtml();

$myPageBottomData = new PageBottomData();
echo $myPageBottomData->getPublicHtml();
?>
