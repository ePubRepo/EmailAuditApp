<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('../../emailarchive/inc_email_archive_bootstrap.php');

$myPageMetaData = new PageMetaData();
$myPageMetaData->setTitle('Email Archive App - Help');
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
<h2>Help</h2>
<h3>Pre-Requisites</h3>
<ol>
<li><span class="underline">Google Apps&trade; for Business, Education, and ISP</span> - The Email Archive App uses the <a href="http://code.google.com/googleapps/domain/audit/docs/1.0/audit_developers_guide_protocol.html">Email Audit API</a> to provide you a copy of a user's mailbox. Google only provides this API for Google Apps domains that are Google Apps for Business, Education, or ISP editions.</li>
<li><span class="underline">Domin Administrator</span> - You must be a Google Apps domain administrator to grant the Email Archive App permissions to access your data at Google via Google's APIs.</li>
<li><span class="underline">Provisioning API Enabled</span> - The Email Archive App uses Google's <a href="http://code.google.com/googleapps/domain/audit/docs/1.0/audit_developers_guide_protocol.html">Email Audit API</a> to provide you a copy of a user's mailbox. This API requires another Google API, the Provisioning API, to be <a href="http://support.google.com/a/bin/answer.py?hl=en&answer=60757">enabled</a> in your Google Apps Control Panel.</li>
<li><span class="underline">Chrome or Firefox</span> - The Email Archive App is written for use with Google Chrome or Mozilla Firefox. You can use any browser you like, but the Email Archive App may not work with your browser of choice unless it is Google Chrome or Mozilla Firefox.</li>
</ol>

<h3>Using the App</h3>
<p>These steps are written for individuals who have just landed upon the Email Archive App and have not used it yet. If you have logged into the App and granted access for the App to access your data, these instructions may not apply in the exact same order.</p>
<ol>
<li><span class="underline">Visit the Email Archive App</span> - Visit www.apps-apps.info/emailarchive/</li>
<li><span class="underline">Launch the Email Archive App</span> - Click the "Launch App" button on the right side of the homepage. If you do not see the "Launch App" button, you are probably already signed into the app or you navigated to the wrong page.</li>
<li><span class="underline">Grant Access to the Email Archive App</span> - After clicking on the "Launch App" button, the Email Archive App will take you to an official Google page where you will need to grant access to access your data through Google's APIs.<br /><img src="image/app-access-request.png" height="180" width="436" /></li>
<li><span class="underline">Select a User's Mail to Archive</span> - Once you have fully authenticated and the Email Archive app fully loads, enter the full email address of the user you want to search for and click the "Export" button.<br /><img src="image/search-box.png" width="570" height="53" /></li>
<li><span class="underline">Wait for Google to Finish Processing a Mailbox Export Request</span> - Once Google successfully receives the mailbox export request, you will see the request in the export history with a status of "Pending".</li>
<li><span class="underline">Download the Mailbox</span> - Once the mailbox is fully ready for export, a process that can take anywhere from four hours to four days, you will see a Download hyperlink next to the user's email address in the export history.</li>
</ol>

<h3>FAQ</h3>
<ul>
<li><span class="faq-question">Why does it take so long to obtain a copy of a mailbox export?</span><br />
<span class="faq-answer">The creation of the mailbox export is done entirely by Google, which throttles the export process.</span></li>
<li><span class="faq-question">Can I speed up the length of time it takes to receive a mailbox export?</span><br />
<span class="faq-answer">Unfortunately, no. As the creation of the encrypted mailbox file is done entirely on Google's end, there is nothing that the Email Archive App can do to speed up the process of creating a mailbox export.</span></li>
<li><span class="faq-question">What limits exist on the number of requests I can make?</span><br />
<span class="faq-answer">Google's <a href="http://code.google.com/googleapps/domain/audit/docs/1.0/audit_developers_guide_protocol.html">Email Audit API</a> limits the number of requests per domain to 100. Consequently, you can only request 100 mailbox exports per day.</span></li>
</ul>
</div>
<?php
$myPageBottomEmailarchive = new EmailarchivePageBottom();
echo $myPageBottomEmailarchive->getPublicHtml();

$myPageBottomData = new PageBottomData();
echo $myPageBottomData->getPublicHtml();
?>
