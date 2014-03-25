<?php

namespace phpsec;

class HttpRequestException extends \Exception {}
class HttpRequestInsecureParameterException extends HttpRequestException {}
class BaseURLNotSetException extends HttpRequestException {}

require_once (__DIR__ . '/tainted.php');

/**
 * HttpRequestArray class
 * Wraps $_SERVER in an ArrayAccess interface
 */
abstract class HttpRequestArray implements \ArrayAccess
{
	protected $data;

	public function __construct($data = null)
	{
		$this->data = $data;
	}

	public function offsetSet($offset, $value)
	{
		if (is_null($offset))
			$this->data[] = $value;
		else
			$this->data[$offset] = $value;
	}

	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

	public function offsetGet($offset)
	{
		//TODO: some of the values will be messed up after using ___r method to retrive internal URL.
		//override them if BaseURL is set (use framework for testing)
		if (isset($this->data[$offset]))
		{
			if (substr($offset,0,4) === 'HTTP')
				return new TaintedString($this->data[$offset]);
			else
				return $this->data[$offset];
		}
		else
			return NULL;
	}
}

/**
 * HttpRequest class
 * Wrapper class to securely process HTTP request parameters
 */
class HttpRequest extends HttpRequestArray
{
	/**
	 * Protocol constants
	 */
	const PROTOCOL_CLI   = 'cli';
	const PROTOCOL_HTTP  = 'http';
	const PROTOCOL_HTTPS = 'https';

	/**
	 * Request method constants
	 */
	const METHOD_GET    = 'GET';
	const METHOD_POST   = 'POST';
	const METHOD_PUT    = 'PUT';
	const METHOD_DELETE = 'DELETE';
	const METHOD_PATCH  = 'PATCH';
	const METHOD_OTHER  = 'OTHER';

	/**
	 * The application can set this to point to the base URL of the application
	 * (e.g example.com/app/resides/here/) so that some functions work.
	 * @note don't forget the trailing slash
	 * @var string
	 */
	protected static $BaseURL=null;
	protected static $BasePath=null;

	/**
	 * Applications should use this to set base url of the website,
	 * so that Internal* functions work properly.
	 */
	public static function SetBaseURL($BaseURL)
	{
		self::$BaseURL=$BaseURL;
		self::$BasePath=substr($BaseURL,strpos($BaseURL,"/",strpos($BaseURL,"://")+4)+1);
	}

	/**
	 * Checks if script is being called from command line
	 * @return boolean
	 */
	public static function isCLI()
	{
		if (php_sapi_name() === self::PROTOCOL_CLI || !isset($_SERVER['REMOTE_ADDR']))
			return true;
		else
			return false;
	}

	/**
	 * Returns IP address of client
	 * @return  string IP
	 */
	static function IP()
	{
		if (self::isCLI())
			return '127.0.0.1';
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Returns the current URL
	 * @return  string URL
	 */
	static function URL($QueryString=true)
	{
		if (self::isCLI())
			return null;
		if ($QueryString && self::QueryString() )
			return (self::Protocol()."://".self::ServerName().self::PortReadable().self::Path()."?".self::QueryString());
		else
			return (self::Protocol()."://".self::ServerName().self::PortReadable().self::Path());
	}

	/**
	 * HTTP Host, aka Domain name
	 *
	 * @return string Host
	 */
	static function Host()
	{
		if (self::IsCLI())
			return "localhost";
		return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
	}

	static function ServerName()
	{
		return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';

	}

	/**
	 * Returns protocol of the client connection, HTTP/HTTPS
	 * @return string Protocol
	 */
	static function Protocol()
	{
		if (self::isCLI())
			return self::PROTOCOL_CLI;
		$x = (isset($_SERVER['HTTPS'])) ? $_SERVER['HTTPS'] : '';
		if ($x == "off" or $x == "")
			return self::PROTOCOL_HTTP;
		else
			return self::PROTOCOL_HTTPS;
	}

	/**
	 * Checks if protocol is HTTPS
	 * @return boolean
	 */
	static function isHTTPS()
	{
		return (self::Protocol() === self::PROTOCOL_HTTPS);
	}

	/**
	 * Checks if protocol is HTTP
	 * @return boolean
	 */
	static function isHTTP()
	{
		return (self::Protocol() === self::PROTOCOL_HTTP);
	}

	/**
	 * Changes protocol/scheme of current URL
	 *
	 * @return string URL
	 */
	static function ChangeProtocol()
	{
		if (self::isCLI())
			return self::URL();
		if (self::isHTTPS())
			return (self::PROTOCOL_HTTP."://".self::ServerName().self::PortReadable().self::RequestURI());
		else
			return (self::PROTOCOL_HTTPS."://".self::ServerName().self::PortReadable().self::RequestURI());
	}

	/**
	 * Returns port of client connection
	 * @return string Port
	 */
	static function Port()
	{
		if (self::isCLI())
			return NULL;
		return isset ($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : "";
	}

	/**
	 * @return null|string
	 */
	static function PortReadable()
	{
		if (self::isCLI()) {
			return NULL;
		}
		$port = self::Port();
		if ($port == "80" && strtolower(self::Protocol()) == self::PROTOCOL_HTTP) {
			$port = "";
		} elseif ($port == "443" && strtolower(self::Protocol()) == self::PROTOCOL_HTTPS) {
			$port = "";
		} else {
			$port = ":" . $port;
		}

		return $port;
	}

	/**
	 * Returns the URI for current script
	 * @return  string RequestURI
	 */
	static function RequestURI()
	{
		if (self::isCLI())
			return NULL;
		return isset ($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
	}

	/**
	 * Returns the internal request URI, i.e the portion that is
	 * after the application base URL
	 * @return string
	 */
	static function InternalRequestURI()
	{
		if (self::$BaseURL===null)
			throw new BaseURLNotSetException("You should set the base URL to use internal* functions.");
		return substr(self::RequestURI(),strlen(self::$BaseURL));
	}

	/**
	 * Query String, the last part in url after ?
	 *
	 * @return String QueryString
	 */
	static function QueryString ()
	{
		if (self::IsCLI())
			return http_build_query($_GET);
		if (isset($_SERVER['REDIRECT_QUERY_STRING']))
		{
			$a = explode("&", $_SERVER['REDIRECT_QUERY_STRING']);
			$x = array_shift($a);
			return substr($_SERVER['REDIRECT_QUERY_STRING'], strlen($x) + 1);
		}
		else
			return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "";
	}

	/**
	 * Request method, GET/POST/PUT/DELETE/PATCH
	 *
	 * @return string RequestMethod
	 */
	static function Method()
	{
		if (self::IsCLI())
			return self::METHOD_GET;
		switch ($_SERVER['REQUEST_METHOD'])
		{
			case 'GET':
				return self::METHOD_GET;
				break;
			case 'POST':
				return self::METHOD_POST;
				break;
			case 'PUT':
				return self::METHOD_PUT;
				break;
			case 'DELETE':
				return self::METHOD_DELETE;
				break;
			case 'PATCH':
				return self::METHOD_PATCH;
				break;
			default:
				return self::METHOD_OTHER;
				break;
		}
	}

	/**
	 * Request Path, e.g http://somesite.com/this/is/the/request/path/index.php
	 * will return this/is/the/request/path/index.php
	 * @return string Path
	 */
	static function Path()
	{
		if (self::IsCLI())
			return NULL;
		$RequestURI = $_SERVER['REQUEST_URI'];
		if (strpos($RequestURI,"?") !== false)
			$Path = substr($RequestURI,0,strpos($RequestURI,"?"));
		else
			$Path = $RequestURI;
		return $Path;
	}

	/**
	 * Returns the internal path, i.e the portion that is
	 * after the application base URL
	 * @return string
	 */
	static function InternalPath()
	{
		if (self::isCLI()) //CLI-based internal path
			if ($temp=strpos(self::$BasePath,"?"))
				return substr(self::$BasePath,0,$temp);
			else
				return self::$BasePath;
		if (self::$BaseURL===null)
			throw new BaseURLNotSetException("You should set the base URL to use internal* functions.");
		return substr(self::Path(),strlen(self::$BasePath)+1);

	}

	/**
	 * Root of website without trailing slash
	 * @see InternalPath
	 * @return string Root
	 */
	static function Root()
	{
		if (self::IsCLI())
			return NULL;
		$root = self::Protocol()."://".self::Host() . self::PortReadable(). "/" .self::InternalPath();
		return $root;
	}

	/**
	 * Returns the IP address of the server under which the current script is executing.
	 * @return string
	 */
	static function ServerIP()
	{
		#TODO: this is still insecure, find a way to list IP addresses on the system, and match against these, or use online services
		if (self::isCLI())
			return '127.0.0.1';
		return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : NULL;
	}

}

$_SERVER = new HttpRequest($_SERVER);
