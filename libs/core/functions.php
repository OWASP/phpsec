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
