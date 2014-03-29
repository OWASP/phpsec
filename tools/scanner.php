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
	public static $blacklist = array("T_ECHO"=>"echo", "T_PRINT"=>"print","printf","vprintf");



	/**
	 * Array to hold the type/nature of the error.
	 * @var Array
	 */
	public static $warningType = array("w"=>"WARNING", "e"=>"ERROR");



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
			if (pathinfo($file, PATHINFO_EXTENSION)!="php") continue;
			$occurences[$i]['file'] = realpath($file);
			$occurences[$i]['result'] = Scanner::scanFile($file);

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

                $currentTokenNo = 0;    //keeps track of the current token number that is being examined.
		foreach ($allTokens as $token)
		{
			if (!is_array( $token))
			{
                                $currentTokenNo++;
                                continue;
                        }

			$token[0] = token_name($token[0]);
			foreach (self::$blacklist as $k=>$v)
			{
				if (!is_string($k))
					$k="T_STRING";
				if ($token[0]==$k && $token[1]==$v)
				{
					//var_dump($token);
					$line = $token[2];

					$inlineVariable = FALSE;
					$localTokenNo = $currentTokenNo;    //keep a local copy of the current token number.
                                        while($allTokens[$localTokenNo++] != ";")    //search for the token ";" from the current token number.
					{
						if (is_array($allTokens[$localTokenNo]) && ($allTokens[$localTokenNo][0] == T_ENCAPSED_AND_WHITESPACE))
						{
							$inlineVariable = TRUE;
						}
						elseif($allTokens[$localTokenNo] == ".")
						{
							$tempToken = $localTokenNo+1;
							while(isset($allTokens[$tempToken][0]) && ($allTokens[$tempToken][0] == T_WHITESPACE)) {$tempToken++;}
							if(isset($allTokens[$tempToken][0]) && ($allTokens[$tempToken][0] == T_VARIABLE))
							{
								$inlineVariable = TRUE;
							}
						}
					}
                                        $statementTillLine = $allTokens[$localTokenNo][2];  //get the line number where the statement ends.

                                        $content = "";
                                        for($i = $line-1; $i < $statementTillLine; $i++)    //get contents of all the lines which are related to the statement.
                                        {
                                                $content .= preg_replace('/(\t|\n)+/', "", $filecontents[$i]);
                                        }

					$occurences[$count]["CONTENT"] = $content;
					$occurences[$count]["LINE"] = $line;
					$occurences[$count]["ERROR"] = $token[0];
					if($inlineVariable)
						$occurences[$count]["TYPE"] = Scanner::$warningType["e"];
					else
						$occurences[$count]["TYPE"] = Scanner::$warningType["w"];

					$count++;
				}
			}

                        $currentTokenNo++;
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
		if ( ($error == "T_ECHO") || ($error == "T_PRINT") || ($error == "T_STRING") )
		{
			return "Keyword [{$error}] found in this statement. Using this statement can cause injection attacks!";
		}
                else
                {
                        return $error;
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
			foreach ($listoferrors['result'] as $individualErrors)
			{
				if (count($individualErrors) == 0)
					continue;

				$file = $listoferrors['file'];
				$line = $individualErrors["LINE"];
				$content = $individualErrors["CONTENT"];
				$errorType = $individualErrors["ERROR"];
				$errorNature = $individualErrors["TYPE"];

				echof("FILE:\t?\n", $file);
				echof("LINE:\t?\n", $line);
				echof("ERROR:\t?\n", Scanner::getErrorMessage($errorType));
				echof("CONTENT:\t?\n", $content);
				echof("TYPE:\t?\n\n", $errorNature);
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
			foreach ($listoferrors['result'] as $individualErrors)
			{
				if (count($individualErrors) == 0)
					continue;

				$file = $listoferrors['file'];
				$line = $individualErrors["LINE"];
				$content = $individualErrors["CONTENT"];
				$errorType = $individualErrors["ERROR"];
				$errorMessage = Scanner::getErrorMessage($errorType);
				$errorNature = $individualErrors["TYPE"];

				echof("?:?:?:?:\t?\n?\n\n", $errorNature, $file, $line, $errorType, $content, $errorMessage);
			}
		}
	}
}

?>
