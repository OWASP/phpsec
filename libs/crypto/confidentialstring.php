<?php
namespace phpsec;



/**
 * Parent Exception
 */
class FileExceptions extends \Exception {}



/**
 * Child Exceptions
 */
class FileNotWritable extends FileExceptions {}		//The file does not have write permissions in it



class Encryption
{



	/**
	 * Cipher to be used for encryption.
	 * @var string		Name of the cipher
	 */
	private static $cipher = MCRYPT_RIJNDAEL_256;



	/**
	 * Key to be used for encryption/decryption.
	 * @var string		The key for encryption/decryption
	 */
	private static $key = "qgyXyjD5YpF";



	/**
	 * Mode to be used for encryption/decryption such as "ebc", "cbc" etc.
	 * @var string		Mode for encryption/decryption
	 */
	private static $mode = "cbc";



	/**
	 * IV to be used for modes other than "ebc".
	 * @var string		The initial vector for encryption/decryption
	 */
	private static $iv = "12345678901234567890123456789012";



	/**
	 * Function to get the value of cipher.
	 * @return string	Returns the name/value of the cipher in use
	 */
	public static function getCipher()
	{
		return Encryption::$cipher;
	}



	/**
	 * Function to get the value of key.
	 * @return string	Returns the key of the cipher in use
	 */
	public static function getKey()
	{
		return Encryption::$key;
	}



	/**
	 * Function to get the value of encryption/decryption mode such as "ebc", "cbc" etc.
	 * @return string	Returns of the mode used in cipher
	 */
	public static function getMode()
	{
		return Encryption::$mode;
	}



	/**
	 * Function to get the value of IV.
	 * @return string	Returns the IV used for the current cipher
	 */
	public static function getIV()
	{
		return Encryption::$iv;
	}
}



/**
 * Function to encrypt the sensitive data on its first run. For rest of the run, this function decrypts the encrypted data for use.
 * @return string		The string in plain-text
 * @throws FileNotWritable	Thrown when the file is not writable
 */
function confidentialString()
{
	$trace = debug_backtrace(); //get the trace of this function call.

	//From this trace, find the proper sub-array which contains this function call. That call would be when the array's function parameter would contain this __FUNCTION__ value.
	$arraySlot = null;
	foreach ($trace as $count => $oncCall) {
		if ($oncCall['function'] == __FUNCTION__) {
			$arraySlot = $count;
			break;
		}
	}

	//If no value is passed to this function, then there is nothing to protect. Hence exit.
	if (count($trace[$arraySlot]['args']) == 0) {
		return "";
	}


	//Every encrypted string will contain ":" in the beginning. If this character is found in the string, then this is an encrypted string.
	if ($trace[$arraySlot]['args'][0][0] == ":") {
		$decodedString = substr($trace[$arraySlot]['args'][0], 1); //remove the ":" character form the string.
		$decodedString = base64_decode($decodedString); //the string was base64 encoded. Hence decode it back.

		$decryptedString = mcrypt_decrypt(Encryption::getCipher(), Encryption::getKey(), $decodedString, Encryption::getMode(), Encryption::getIV()); //decrypt the string.

		return unserialize(rtrim($decryptedString, "\0")); //return the decrypted string.
	}
	else //This is the first run of this function for this string. We know this because this string is not encrypted.
	{
		$origString = $trace[$arraySlot]['args'][0]; //store the original value.

		$encryptedString = mcrypt_encrypt(Encryption::getCipher(), Encryption::getKey(), serialize($origString), Encryption::getMode(), Encryption::getIV()); //encrypt the value.
		$encryptedString = base64_encode($encryptedString); //base 64 encode it.
		$encryptedString = ":" . $encryptedString; //append ":" at the beginning of the encrypted string.

		$fileData = file($trace[$arraySlot]['file']); //get file contents as an array.

		$prevLine = $fileData[(int)$trace[$arraySlot]['line'] - 1]; //get the line that needs to be replaced i.e. the string that contains the plain-text sensitive data.
		$functionName = str_replace(__NAMESPACE__ . "\\", '', __FUNCTION__); //calculate the function name of this function (without any namespace).
		$pos = strpos($prevLine, $functionName); //find the position of this function-name in the original string.
		$endPos = strpos($prevLine, ")", $pos); //search where this function ends, but start the search from the start of the function.

		$newLine = substr($prevLine, 0, $pos) . $functionName . "('{$encryptedString}')"; //generate the new line i.e. with encrypted String.

		$fileData[(int)$trace[$arraySlot]['line'] - 1] = $newLine . substr($prevLine, $endPos + 1); //replace the old line with the new line.
		$fileData = implode("", $fileData); //get the data from the array.

		//check if file is writable or not.
		if (!is_writable($trace[$arraySlot]['file'])) {
			throw new FileNotWritable("ERROR: This file is not Writable!!");
		}

		//write this new data to file.
		$fp = fopen($trace[$arraySlot]['file'], 'w');
		fwrite($fp, $fileData);
		fclose($fp);

		//return the un-encrypted string for use.
		return $origString;
	}
}
