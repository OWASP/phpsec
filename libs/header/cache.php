<?php

namespace phpsec;

/**
 * Required classes
 */
require_once(__DIR__ . '/header.php');

class CacheException extends \Exception {}

/**
 * Cache-control class
 * 
 */
class Cache extends Header
{
	const CONTROL_PUBLIC			=	"public";
	const CONTROL_PRIVATE			=	"private";
	const CONTROL_NO_CACHE			=	"no-cache";
	const CONTROL_NO_STORE			=	"no-store";
	const CONTROL_MUST_REVALIDATE	=	"must-revalidate";

	public static function digest($content)
	{
		return sha1($content);
	}

	public static function setControl($value)
	{
		if (!Header::isSent())
		{
			$header = new static ("Cache-control", $value);
			$header->set();
			return $header;
		}
	}

	public static function setExpiration($offset)
	{
		if (!Header::isSent())
		{
			$date = gmdate ("D, d M Y H:i:s", time() + $offset);
			$header = new static ("Expires", $date);
			$header->set();
			return $header;
		}
	}

	/**
	 * Deprecated. Shift to `cache-control`
	 */
	public static function setPragma($value)
	{
		if (!Header::isSent())
		{
			$header = new static ("Pragma", $value);
			$header->set();
			return $header;
		}
	}
}

Cache::setControl("max_age=21736");