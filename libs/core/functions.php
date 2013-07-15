<?php
namespace phpsec;
class echofException extends \Exception {}
/**
 * Returns XSS-safe equivalent of string
 * @param mixed $data
 */
function echo_ret($data)
{
	if (func_num_args()>1)
	{
		$args=func_get_args();
		$out=array();
		foreach ($args as $arg)
			$out[]=echo_ret ($arg);
		return implode("",$out);
	}
	if (defined("ENT_HTML401"))
		$t=htmlspecialchars($data,ENT_QUOTES | ENT_HTML401,"UTF-8");
	else
		$t=htmlspecialchars($data,ENT_QUOTES,"UTF-8");
	
	return $t;
}


/** @decontaminated_start **/
/**
 * XSS-safe replacement for echo.
 * Basically you should never use echo or print in your project, instead use php tags and this.
 * @param $data the data to output
 * @param mixed $data
 */
function exho($data)
{
	echo echo_ret($data);
}
/**
 * XSS-safe replacement for echo, with formatting and ability to dump elements and attributes
 * Usage: echo_param("Hello, you're number <strong>?</strong>",$number);
 * @param $string the format string
 */
function echof($string)
{
	if (substr_count($string,"?")!==func_num_args()-1)
		throw new echofException("Number of arguments doesn't match number of ?s in format string.");
	$out=$string;
	$args=func_get_args();
	array_shift($args);
	foreach ($args as $arg)
	{
		$formatPosition=strpos($out,"?");
		$out=substr($out,0,$formatPosition).echo_ret($arg).substr($out,$formatPosition+1);
	}
	echo($out);
}
/**
 * This one replaces NewLines with <br/>
 * @see echo
 * @param unknown $data
 */
function echo_br($data)
{
	echo nl2br(echo_ret($data));	
}

/** @decontaminated_end **/


class Encryption
{
	private static $cipher = MCRYPT_RIJNDAEL_256;
	private static $key = "qgyXyjD5YpF";
	private static $mode = "cbc";
	private static $iv = "akfhaR*(3RFn";
	
	public static function getCipher()
	{
		return Encryption::$cipher;
	}
	
	public static function getKey()
	{
		return Encryption::$key;
	}
	
	public static function getMode()
	{
		return Encryption::$mode;
	}
	
	public static function getIV()
	{
		return Encryption::$iv;
	}
}

function confidentialString()
{
	$trace = debug_backtrace();
	
	$arraySlot = 0;
	
	if ( count($trace[$arraySlot]['args']) == 0 )
		return "";
	
	if ( $trace[$arraySlot]['args'][0][0] == ":" )
	{
		$decodedString = substr($trace[$arraySlot]['args'][0], 1);
		$decodedString = base64_decode($decodedString);
		
		$decryptedString = mcrypt_decrypt(Encryption::getCipher(), Encryption::getKey(), $decodedString, Encryption::getMode(), Encryption::getIV());
		
		return $decryptedString;
	}
	else
	{
		$origString = $trace[$arraySlot]['args'][0];
		
		$encryptedString = mcrypt_encrypt(Encryption::getCipher(), Encryption::getKey(), $trace[$arraySlot]['args'][0], Encryption::getMode(), Encryption::getIV());
		$enc = base64_encode( $encryptedString );
		$encryptedString = ":" . $encryptedString;
		
		$fileData = file(__FILE__);
		
		$prevLine = $fileData[(int)$trace[$arraySlot]['line'] - 1];
		$pos = strpos($prevLine, __FUNCTION__);
		
		$newLine = substr($prevLine, 0, $pos) . __FUNCTION__ . "('{$enc}');";
		
		$fileData[(int)$trace[$arraySlot]['line'] - 1] = $newLine;
		$fileData = implode("", $fileData);
		
		$fp = fopen(__FILE__, 'w');
		fwrite($fp, $fileData);
		fclose($fp);
		
		return $origString;
	}
}