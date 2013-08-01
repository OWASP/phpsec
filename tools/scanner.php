<?php
namespace phpsec;


/**
 * Parent Exception
 */
class ScannerException extends \Exception {}


/**
 * Child Exceptions
 */
class DirectoryORFileNotFoundException extends ScannerException {}



class Scanner
{
	
	
	/**
	 * Array to hold all the words that are considered unsafe.
	 * @var Array
	 */
	public static $blacklist = array("T_ECHO", "T_PRINT");
	
	
	
	/**
	 * Function to start a scan in a directory.
	 * @return Array
	 * @throws DirectoryNotFoundException
	 */
	public static function scanDir($parentDirectory)
	{
		$occurences = array(array());
		
		//if the directory/file does not exists, then throw and error.
		if ( !file_exists( $parentDirectory ) )
		{
			throw new DirectoryORFileNotFoundException("ERROR: Directory not found!");
		}
		
		
		//get the list of all the files inside this directory.
		$allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($parentDirectory));
		
		$fileList = array();
		
		//remove (.), (..) and directories from the list of all files so that only files are left.
		while($allFiles->valid())
		{
			if (!$allFiles->isDot())	//remove present director[.] and parent directory[..]
			{
				if (!is_dir($allFiles->key()))
				{
					array_push($fileList, $allFiles->key());
				}
			}

			$allFiles->next();
		}
		
		$i = 0;
		foreach ($fileList as $file)	//add errors found to the results.
		{
			$occurences[$i][0] = Scanner::scanFile($file);
			$occurences[$i][1] = realpath($file);
			
			$i++;
		}
		
		return $occurences;
	}
	
	
	
	/**
	 * Function to start a scan in a file.
	 * @param String $pathToFile
	 * @return Array
	 */
	public static function scanFile($pathToFile)
	{
		//if the directory/file does not exists, then throw and error.
		if ( !file_exists( $pathToFile ) )
		{
			throw new DirectoryORFileNotFoundException("ERROR: Directory not found!");
		}
		
		$filecontents = file($pathToFile);
		
		$allTokens = token_get_all( file_get_contents($pathToFile) );

		//variable to hold the results.
		$occurences = array(array());
		$count = 0;
		
		foreach ($allTokens as $token)
		{
			if (!is_array( $token))
				continue;
			
			$token[0] = token_name($token[0]);
			
			if (  in_array( $token[0], Scanner::$blacklist))
			{
				$line = array_pop($token);
				$occurences[$count]["CONTENT"] = preg_replace('/(\t|\n)+/', "", $filecontents[$line-1]);
				$occurences[$count]["LINE"] = $line;
				$occurences[$count]["ERROR"] = $token[0];
				
				$count++;
			}
		}
		
		return $occurences;
	}
	
	
	
	/**
	 * Function to get custom error message.
	 * @param String $error
	 * @return String
	 */
	public static function getErrorMessage($error)
	{
		if ( ($error == "T_ECHO") || ($error == "T_PRINT") )
		{
			return "Keyword [{$error}] found in this statement. Using this statement can cause injection attacks!";
		}
	}
	
	
	
	/**
	 * Function to display errors.
	 * @param Array $errors
	 * @param String $customErrorMessage
	 */
	public static function displayErrors($errors)
	{
		require_once (__DIR__ . '/../libs/core/functions.php');
		
		foreach ($errors as $listoferrors)
		{
			foreach ($listoferrors[0] as $individualErrors)
			{
				if (count($individualErrors) == 0)
					continue;
				
				$file = $listoferrors[1];
				$line = $individualErrors["LINE"];
				$content = $individualErrors["CONTENT"];
				$errorType = $individualErrors["ERROR"];

				echof("FILE:\t?\n", $file);
				echof("LINE:\t?\n", $line);
				echof("ERROR:\t?\n", Scanner::getErrorMessage($errorType));
				echof("CONTENT:\t?\n\n", $content);
			}
		}
	}
	
	
	
	/**
	 * Function to display error in GCC style.
	 * @param Array $errors
	 * @param String $customErrorMessage
	 */
	public static function displayGCCStyleOutput($errors)
	{
		require_once (__DIR__ . '/../libs/core/functions.php');
		
		foreach ($errors as $listoferrors)
		{
			foreach ($listoferrors[0] as $individualErrors)
			{
				if (count($individualErrors) == 0)
					continue;
				
				$file = $listoferrors[1];
				$line = $individualErrors["LINE"];
				$content = $individualErrors["CONTENT"];
				$errorType = $individualErrors["ERROR"];
				$errorMessage = Scanner::getErrorMessage($errorType);
				
				echof("?:?:?:\t?\n?\n\n", $file, $line, $errorType, $content, $errorMessage);
			}
		}
	}
}

?>
