<?php
// Copyright 2011. Eric Beach. All Rights Reserved.

class NoVersionNumberException extends Exception
{
	public function __construct($msg)
	{
		parent::__construct($msg);
	}
}

abstract class FileVersioner
{
	/**
	 * The value of this "constant" is set by
	 * {@link FileVersioner::loadFileVersionsFromFile()}.
	 *
	 * @var string
	 */
	private static $pathToFileVersionsFile;
	
	/**
	 * The file versions array itself.
	 *
	 * The array is structured like this:
	 * 
	 * <pre>
	 * array(
	 *   '/home/jplatinu/public_html/global/styles/style.css' => array(
	 *     'ver' => 14,
	 *     'mtime' => 1398437145  // where this value is a timestamp in seconds
	 *   ),
	 *   // ...
	 * );
	 * </pre>
	 * 
	 * This value is loaded in by
	 * {@link FileVersioner::loadFileVersionsFromFile()}.
	 * 
	 * @var array
	 */
	private static $fileVersions;
	
	public static function getPathToFile($filePathFromWwwRoot)
	{
		// ensure a preface '/'	
		if (substr($filePathFromWwwRoot, 0, 1) != '/')
		{
			 $filePathFromWwwRoot = '/' . $filePathFromWwwRoot;
		}
		
		return $filePathFromWwwRoot
			. (strpos($filePathFromWwwRoot, '?') === false ? '?' : '&')
			. 'v='
			. self::getFileVersionNumber($filePathFromWwwRoot);
	}
	
	/**
	 * Returns the file version number to be associated with the given file
	 *
	 * @param string $filePath path to the file for which we should return the
	 *                     version number.
	 * @return int the version number associated with this most recent version
	 *             of the requested file.
	 * @throws NoVersionNumberException if the given file does not have a
	 *             version number associated with it, which may be because it
	 *             does not exist.
	 */
	public static function getFileVersionNumber($filePath)
	{
		self::loadFileVersionsFromFile();
		$file = self::convertToAbsoluteFilePath($filePath);

		// Make sure we check to see if the file has been updated
		// TODO Ultimately, it would be nice to do this "offline"--i.e.,
		//      for a cron job to do this.
		self::updateFileVersionNumberIfModified($filePath);
		
		if (isset(self::$fileVersions[$file]))
		{
			if (isset(self::$fileVersions[$file]['ver']))
			{
				return self::$fileVersions[$file]['ver'];
			}
			else
			{
				Logger::add_error_log_entry(__FILE__, __LINE__, 'Static file name found, but no `ver` element set in the array. File: ' . $file);
				throw new NoVersionNumberException($file);
			}
		}
		else
		{
			Logger::add_error_log_entry(__FILE__, __LINE__, 'Static file name is not listed in array. File: ' . $file);
			throw new NoVersionNumberException($file);
		}
	}
	
	/**
	 * Updates the file versions array (both in memory and on disk) if the file
	 * identified by the given file path has been modified more recently than
	 * the modification time specified in the file versions array.
	 *
	 * @param string $filePath path to the file, relative to the DIR_WWW
	 *                         directory.
	 */
	public static function updateFileVersionNumberIfModified($filePath)
	{
		self::loadFileVersionsFromFile();
		$file = self::convertToAbsoluteFilePath($filePath);
		
		if (!is_readable($file))
		{
			return;
		}
		
		$fp = fopen($file, 'r');
		$fstat = fstat($fp);
		fclose($fp);
		
		if (isset(self::$fileVersions[$file]['mtime']))
		{
			if ($fstat['mtime'] > self::$fileVersions[$file]['mtime'])
			{
				// The actual file has been updated more recently than the date
				// stored in our cache. So, update the time in our cache and update
				// our version number.
				self::$fileVersions[$file]['mtime'] = $fstat['mtime'];
				self::$fileVersions[$file]['ver']++;
				
				self::saveFileVersionsToFile();
			}
		}
		else
		{
			// There is no entry in the fileVersions array for the given file.
			// Create one, and label it as version 1.
			self::$fileVersions[$file] = array();
			self::$fileVersions[$file]['mtime'] = $fstat['mtime'];
			self::$fileVersions[$file]['ver'] = 1;
			
			self::saveFileVersionsToFile();
		}
	}
	
	private static function convertToAbsoluteFilePath($relativeFilePath)
	{
		return GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'www' . $relativeFilePath; 
	}
	
	private static function loadFileVersionsFromFile()
	{
		self::$pathToFileVersionsFile = GlobalConstants::getAbsolutePathToAppAppsRootDirectory() . 'common/datastore/file_versioner/versions.serialized';
		
		if (is_null(self::$fileVersions) || count(self::$fileVersions) == 0)
		{
			if (file_exists(self::$pathToFileVersionsFile))
			{
				if (is_readable(self::$pathToFileVersionsFile))
				{
					self::$fileVersions = unserialize(file_get_contents(self::$pathToFileVersionsFile));
					
					if (self::$fileVersions === false)
					{
						Logger::add_error_log_entry(__FILE__, __LINE__, 'The file that is supposed to contain the serialized file versions array isn\'t actually serializable. This file is problematic: ' . self::$pathToFileVersionsFile);
						self::$fileVersions = array();
					}
				}
				else
				{
					Logger::add_error_log_entry(__FILE__, __LINE__, 'The file containing the serialized file versions array exists but is not readable. This file needs to be readable: ' . self::$pathToFileVersionsFile);
					self::$fileVersions = array();
				}
			}
			else
			{
				Logger::add_info_log_entry(__FILE__, __LINE__, 'The file that is supposed to contain the serialized file versions array does not exist: ' . self::$pathToFileVersionsFile);
				self::$fileVersions = array();
			}
		}
	}
	
	private static function saveFileVersionsToFile()
	{
		if (!is_array(self::$fileVersions))
		{
			throw new IllegalStateException('The value of FileVersioner::$fileVersions is NOT an array.');
		}
		
		$return = file_put_contents(self::$pathToFileVersionsFile, serialize(self::$fileVersions));
		
		if ($return === false)
		{
			Logger::add_error_log_entry(__FILE__, __LINE__, 'Cannot write to the path that is supposed to store the serialized file versions array: ' . self::$pathToFileVersionsFile);
		}
	}
}

?>
