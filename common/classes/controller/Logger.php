<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

abstract class Logger
{	
	const INFO_LOG_FILENAME = 'info_log.txt';
	const WARNING_LOG_FILENAME = 'warning_log.txt';
	const ERROR_LOG_FILENAME = 'error_log.txt';
	
	public function add_info_log_entry($log_entry, $log_file = self::INFO_LOG_FILENAME)
	{
		/*
		 * Only log detailed info level entries if the global setting is enabled
		 */
		if (GlobalConstants::isDetailedInfoLoggingEnabled() === false)
		{
			return false;
		}
		self::add_log_entry($log_entry, $log_file);	
	}

	public function add_warning_log_entry($log_entry, $log_file = self::WARNING_LOG_FILENAME)
	{
		self::add_log_entry($log_entry, $log_file);
		self::add_log_entry($log_entry, self::INFO_LOG_FILENAME);
	}
	
	public function add_error_log_entry($log_entry, $log_file = self::ERROR_LOG_FILENAME)
	{
		self::add_log_entry($log_entry, $log_file);
		self::add_log_entry($log_entry, self::INFO_LOG_FILENAME);
	}
	
	private function add_log_entry($log_entry, $log_file)
	{
		//To Do: Translate APPLICATION CONSTANT prefix to appropriate root directory call
		$fp = fopen(GlobalConstants::getAbsolutePathToEmailArchiveRootDirectory() . GlobalConstants::getLogsFoldername() . $log_file, 'a');
		$text_to_write = date("Y-m-d H:i:s") . ' *** ' . AccessIdentityController::getPurportedEmailFromHttpRequest(). ' *** ' . $_SERVER['REMOTE_ADDR'] . ' *** ' . $log_entry . "\n";
		fwrite($fp, $text_to_write);
		fclose($fp);
	}
}

?>
