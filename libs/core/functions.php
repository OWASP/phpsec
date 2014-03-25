<?php
namespace phpsec;
class echofException extends \Exception {}
/**
 * Returns XSS-safe equivalent of string
 * @param mixed $data
 */
function xss_safe($data)
{
	if (func_num_args()>1)
	{
		$args=func_get_args();
		$out=array();
		foreach ($args as $arg)
			$out[]=xss_safe ($arg);
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
	echo xss_safe($data);
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
		$out=substr($out,0,$formatPosition).xss_safe($arg).substr($out,$formatPosition+1);
	}
	echo($out);
}
/**
 * Safe printf. Escapes all arguments
 * The format string should not contain any concatenations or variables, just plain text
 * @param string $formatString
 */
function printf($formatString)
{
	$args=func_get_args();
	$flag=0;
	foreach ($args as &$arg)
	{
		if (!$flag++) continue; //skip first arg, format str
		$arg=xss_safe($arg);
	}
	call_user_func_array("\\printf", $args);
}
/**
 * Safe vprintf. Escapes all arguments
 * The format string should not contain any concatenations or variables, just plain text
 * @param string $formatString
 * @param array args
 */
function vprintf($formatString,$args)
{
	foreach ($args as &$arg)
		$arg=xss_safe($arg);
	call_user_func_array("\\vprintf", array($formatString,$args));
}

/**
 * This one replaces NewLines with <br/>
 * @see echo
 * @param unknown $data
 */
function echo_br($data)
{
	echo nl2br(xss_safe($data));
}
/**
 * exho alias
 * @see exho
 * @param string $data
 */
function echos($data)
{
	exho($data);
}
/** @decontaminated_end **/
