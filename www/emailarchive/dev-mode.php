<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once('../../emailarchive/inc_email_archive_bootstrap.php');

EmailArchiveAccessIdentityCheckController::checkAccessIdentityAndPerformRedirection();

DevelopmentModeController::enableDevelopmentMode();
?>
