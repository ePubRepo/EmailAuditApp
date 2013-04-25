<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('../../emailarchive/inc_email_archive_bootstrap.php');

if (isset($_GET['mode'])
	&& $_GET['mode'] == 'signout')
{
	Logger::add_info_log_entry(__FILE__ . __LINE__ . ' Signout Request Triggered');
	AccessIdentityController::revokeIdentitySecretCookie();
	AccessIdentityController::revokeEmailCookie();
	DevelopmentModeController::revokeDevelopmentMode();
}

$myOpenIdSession = new OpenIdRequest();
$myOpenIdSession->setOauth1ConsumerKey(OAuthConstants::getWebApplicationConsumerKey());
$myOpenIdSession->setOauth1Scope('https://apps-apis.google.com/a/feeds/compliance/audit/+https://apps-apis.google.com/a/feeds/user/+https://www.google.com/hosted/services/v1.0/reports/ReportingData');
$myOpenIdSession->setOpenIdAxRequired('email,firstname,lastname');
$myOpenIdSession->setOpenIdReturnUrl('https://www.apps-apps.info/emailarchive/openid_oauth_callback.php');
$myOpenIdSession->setOpenIdRealm(OpenIdConstants::OPENID_REALM);

$myOpenIdSession->performOpenIdDiscovery();
$session_url = $myOpenIdSession->getFullOpenIdRequestUrl();

$myPageMetaData = new PageMetaData();
$myPageMetaData->addCssFile(FileVersioner::getPathToFile('/emailarchive/css/general.css'));
$myPageMetaData->addJavaScriptFile(FileVersioner::getPathToFile('/emailarchive/js/prod/prod.js', true));
echo $myPageMetaData->getPublicHtml();

$myPageTopEmail = new EmailarchivePageTop();
if (strlen(AccessIdentityController::getPurportedEmailFromHttpRequest()) > 0
	&& isset($_GET['mode'])
	&& $_GET['mode'] != 'signout')
{
	$myPageTopEmail->setUsername(AccessIdentityController::getPurportedEmailFromHttpRequest());
}
echo $myPageTopEmail->getPublicHtml();

?>
<noscript>
<p>Apps on www.apps-apps.info require JavaScript enabled in your browser to function properly.</p> 
</noscript>

<div class="center-container">
	<?php
	if (isset($_GET['mode'])
		&& $_GET['mode'] == 'signout')
	{ 
		echo '<div class="main-header-notification">You have been signed out.</div>';
	}
	else if (isset($_GET['mode'])
		&& $_GET['mode'] == 'id_failed')
	{
		echo '<div class="main-header-notification">The identification and authorization flow failed.</div>';
	}
	?>
	<div class="main-header">App to freely export user mailboxes</div>
	<div class="main-body">
	<ul>
	<li><span class="span-header">&raquo; Export User's Email</span><span class="span-content">Download a copy of any user's mailbox without logging into their account or notifying them.</span></li>
	<li><span class="span-header">&raquo; Use for Free</span><span class="span-content">Use the Email Archive App for free and without any advertisements.</span></li>
	<li><span class="span-header">&raquo; Easy to Use</span><span class="span-content">Queue a mailbox for download in less than a minute.</span></li>
	</ul>
	</div>
	<div class="main-right">
	<input type="button" value="Launch App" onclick="window.location='<?php echo $session_url ?>'; return false;" class="button launch-button" />
	</div>
</div>
<?php
$myPageBottomEmailarchive = new EmailarchivePageBottom();
echo $myPageBottomEmailarchive->getPublicHtml();

$myPageBottomData = new PageBottomData();
echo $myPageBottomData->getPublicHtml();
?>
