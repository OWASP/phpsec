<?php
namespace phpsec;


/**
 * Parent Exception
 */
class ScannerException extends \Exception {}


/**
 * Child Exceptions
 */
class DirectoryNotFoundException extends ScannerException {}



class Scanner
{
	
	
	/**
	 * Array to hold all the words that are considered unsafe.
	 * @var Array
	 */
	public static $blacklist = array("echo ", "print_r");
	
	
	
	/**
	 * Variable to hold the progress so far in scanning.
	 * @var int 
	 */
	public static $percentScanned = 0;
	
	
	/**
	 * Variable to keep the number of total files to scan.
	 * @var int
	 */
	protected static $totalFilesToScan = NULL;
	
	
	/**
	 * Variable to keep track of total files that are scanned.
	 * @var int
	 */
	private static $filesScanned = 0;
	
	
	
	/**
	 * Function to start a scan in a directory.
	 * @return Array
	 * @throws DirectoryNotFoundException
	 */
	public static function scanDir($parentDirectory)
	{
		$occurences = array(array());
		
		//if the directory does not exists, then throw and error.
		if ( !file_exists( $parentDirectory ) )
		{
			throw new DirectoryNotFoundException("ERROR: Directory not found!");
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
					array_push(&$fileList, $allFiles->key());
				}
			}

			$allFiles->next();
		}
		
		Scanner::$totalFilesToScan = count($fileList);
		
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
		//get the contents of the file.
		$fileContents = file($pathToFile);
		
		//variable to hold the results.
		$occurences = array();
		
		//make a regular expression for all the blacklisted words.
		$regex = '/(' . implode('|', Scanner::$blacklist) . ')+/';
		
		//This variable will keep track of the line numbers.
		$lineNo = 0;
		foreach ($fileContents as $line)	//take each line of file and search for the pattern
		{
			if (  preg_match( $regex, $line))	//If pattern is found
			{
				//normalize extra spaces
				$line = preg_replace('/(\t|\n)+/', "", $line);
				
				array_push(&$occurences, htmlspecialchars_decode( $line . "\t[LINE: " . ($lineNo+1) . "]" ) );	//insert that line and the line number in the results.
			}
			
			$lineNo++;
		}
		
		Scanner::$percentScanned = round(((Scanner::$filesScanned+1)/Scanner::$totalFilesToScan), 2) * 100;	//calculate progress.
		Scanner::$filesScanned ++;
		
		return $occurences;
	}
}

?>
