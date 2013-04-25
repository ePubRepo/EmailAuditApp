<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

require_once('../../emailarchive/inc_email_archive_bootstrap.php');

$myPageMetaData = new PageMetaData();
$myPageMetaData->setTitle('Email Archive App - Terms & Privacy');
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
<h2>Terms & Privacy</h2>
<h3>Terms</h3>
<ul>
<li>No Warranties - To the maximum extent permitted by law, the material and functionality on this website (including all content, functions, services, materials and information made available herein or accessed by means hereof) are provided as is, without warranties of any kind, either express or implied, including but not limited to, warranties of fitness or security for a particular purpose.</li>
<li>Limitation of Liability - To the maximum extent permitted by law, you assume full responsibility and risk of loss resulting from your use of the website and the functionality it provides including any downloads from the website. Under no circumstances shall we or any of our representatives be liable for any indirect, punitive, special or consequential damages.</li>
<li>Changes - We reserve the right to change any of the terms of this Agreement by posting the revised Terms of Use on our Website. This new Agreement will be effective immediately with respect to any continued or new use of this website.</li>
<li>Abuse - We reserve the right to take actions including but not limited to stopping providing service or removing user data permanently for any user, domain, or IP that we believe, in our sole discretion, is abusing our website or services.</li>
</ul>

<h3>Privacy</h3>
<ul>
<li><span class="faq-question">Do you sell any customer data?</span>
<span class="faq-answer">No. We do not sell any customer data.</span></li>
<li><span class="faq-question">What steps do you take to help protect privacy and security?</span>
<span class="faq-answer">1.) Use SSL to encrypt connections between your browser and our server. 2.) Configure our server to purge your data after 30 days.</span></li>
</ul>
</div>
<?php
$myPageBottomEmailarchive = new EmailarchivePageBottom();
echo $myPageBottomEmailarchive->getPublicHtml();

$myPageBottomData = new PageBottomData();
echo $myPageBottomData->getPublicHtml();
?>
