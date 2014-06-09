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

	/**
	 * Generates hash based on content.
	 * Uses SHA-1
	 */
	public static function digest($content)
	{
		return sha1($content);
	}

	/**
	 * Sets the `Cache-Control` header
	 */
	public static function setControl($value)
	{
		if (!Header::isSent())
		{
			$header = new static ("Cache-Control", $value);
			$header->set();
			return $header;
		}
		return false;
	}

	/**
	 * Sets the `Expires` header
	 */
	public static function setExpiration($offset)
	{
		if (!Header::isSent())
		{
			$header;
			if (is_string($offset))
			{
				$header = new static ("Expires", $offset);
			}
			else
			{
				$date = gmdate ("D, d M Y H:i:s", time() + $offset);
				$header = new static ("Expires", $date);
			}
			$header->set();
			return $header;
		}
		return false;
	}

	/**
	 * Sets the `Pragma` header
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
		return false;
	}

	/**
	 * Sets `Cache-Control`,`Pragma` and `Expires` headers to prevent caching
	 */
	public static function setNoCache()
	{
		if (!Header::isSent())
		{
			self::setControl(Cache::CONTROL_PRIVATE . ', ' . Cache::CONTROL_NO_CACHE . ', ' . Cache::CONTROL_NO_STORE . ', ' . Cache::CONTROL_MUST_REVALIDATE);
			self::setPragma(Cache::CONTROL_NO_CACHE);
			self::setExpiration('0');
			return true;
		}
		return false;
	}

	/**
	 * Sets header to return 304 i.e. serve cached content.
	 */
	public static function setNotModified()
	{
		if (!Header::isSent())
		{
			header("Not Modified", true, 304);
			return true;
		}
		return false;
	}

	/**
	 * Sets the `Etag` header
	 */
	public static function setEtag($value)
	{
		if (!Header::isSent())
		{
			$header = new static ("Etag", $value);
			$header->set();
			return $header;
		}
		return false;
	}

	/**
	 * Sets the `Etag` header by passing content as parameter
	 */
	public static function setEtagFromContent($content)
	{
		return self::setEtag(self::digest($content));
	}

	/**
	 * Checks if `if-none-match` header matches `Etag`
	 */
	public static function checkEtagHit($etag = NULL)
	{
		$etagHeader=(isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);
		return $etag == $etagHeader;
	}

	/**
	 * Sets `Etag`,`Cache-Control`,`Expires` headers
	 */
	public static function setEtagAndSupportingHeaders($etag = NULL, $offset = 3600)
	{
		if (!Header::isSent() & !is_null($etag))
		{
			self::setEtag($etag);
			self::setControl(Cache::CONTROL_PUBLIC);
			self::setExpiration($offset);
			return true;
		}
		return false;
	}
}