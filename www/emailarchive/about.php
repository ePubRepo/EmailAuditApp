<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('../../emailarchive/inc_email_archive_bootstrap.php');

$myPageMetaData = new PageMetaData();
$myPageMetaData->setTitle('Email Archive App - About');
$myPageMetaData->addCssFile(FileVersioner::getPathToFile('/emailarchive/css/general.css'));
$myPageMetaData->addJavaScriptFile(FileVersioner::getPathToFile('/emailarchive/js/prod/prod.js', true));
echo $myPageMetaData->getPublicHtml();

$myPageTopEmail = new EmailarchivePageTop();
if (strlen(AccessIdentityController::getPurportedEmailFromHttpRequest()) > 0)
{
	$myPageTopEmail->setUsername(AccessIdentityController::getPurportedEmailFromHttpRequest());
}
echo $myPageTopEmail->getPublicHtml();
?>
<div class="center-container">
<h2>About</h2>
<h3>Email Archive App</h3>
<p>The Email Archive App is a free third-party web application that enables domain administrators of Google Apps&trade; for Business, Education, and ISP accounts to export a copy of a user's mailbox. The Email Archive App uses the <a href="http://code.google.com/googleapps/domain/audit/docs/1.0/audit_developers_guide_protocol.html">Email Audit API</a> to access your mailbox data.</p>
<p>As the <a href="terms_privacy.php">Terms &amp; Privacy</a> indicate, the Email Archive App is offered without any type of warrenty or guarantee, including but not limited to uptime, reliability, or security.</p>

<h3>How the App Works</h3>
<ol>
<li><span class="underline">You : Enable APIs</span> - The Email Archive App relies on the public APIs that Google provides to access the mailbox data on your domain. Consequently, you must enable APIs in your Google Apps control panel by <a href="http://support.google.com/a/bin/answer.py?hl=en&answer=60757">following these instructions</a>.</li>
<li><span class="underline">You : Authorize Access</span> - When you launch the Email Archive App, you will be taken to an official Google page where you will grant the Email Archive App permission to access the necessary Google APIs.<br /><img src="image/app-access-request.png" height="180" width="436" /></li>
<li><span class="underline">Us : Generate GPG Key</span> - Google's <a href="http://code.google.com/googleapps/domain/audit/docs/1.0/audit_developers_guide_protocol.html">Email Audit API</a> requires that we upload a cryptographic key which Google will use to encode your mailbox contents. Consequently, we generate a GPG key for your domain.</li>
<li><span class="underline">Us : Upload GPG Keys to Google</span> - The Email Archive App takes the GPG encryption key generated earlier and uploads it to Google so Google can encrypt your mailbox contents.</li>
<li><span class="underline">Us : Request Mailbox Export From Google</span> - The Email Archive App takes your request for a copy of a user's mailbox and uploads it to Google via Google's <a href="http://code.google.com/googleapps/domain/audit/docs/1.0/audit_developers_guide_protocol.html">Email Audit API</a>. Once Google receives the request, Google will create a copy of the mailbox. This process takes anywhere from four hours to four days, depending upon the size of the mailbox.</li>
<li><span class="underline">Us : Download Encrypted Mailbox Export From Google</span> - Once Google has prepared the encrypted copy of the mailbox you requested, the Email Archive App will download this encrypted file to the servers of the Email Archive App.</li>
<li><span class="underline">Us : Decrypt Exported Mailbox</span> - The Email Archive App will use the GPG key it previously created and uploaded to Google in order to decrypt the encrypted mailbox it just received from Google.</li>
<li><span class="underline">You : Download Decrypted Exported Mailbox</span> - The Email Archive App will allow you to download the decrypted mailbox you requested earlier.</li>
</ol>
</div>
<?php
$myPageBottomEmailarchive = new EmailarchivePageBottom();
echo $myPageBottomEmailarchive->getPublicHtml();

$myPageBottomData = new PageBottomData();
echo $myPageBottomData->getPublicHtml();
?>
