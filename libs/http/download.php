<?php
namespace phpsec;

require_once (__DIR__ . "/../core/functions.php");


/**
 * Parent Exception
 */
class DownloadManagerException extends \Exception {}

/**
 * Child Exceptions
 */
class InvalidFileModifiedDateException extends DownloadManagerException {}


class DownloadManager
{

	/**
	 * To denote the minimum size in bytes for enabling the limitation of speed.
	 * @var int
	 */
	public static $BandwidthLimitInitialSize=10485760; //10MB


	/**
	 * To denote the amount of bytes that must be read. This is also called speed-limiter with which files are read.
	 * @var int
	 */
	public static $BandwidthLimitSpeed=1048576; //1MB


	/**
	 * To determine the type of file from its extension.
	 * @param String $File
	 * @return string
	 */
	public static function MIME ($File)
	{
		$a=explode('.', $File);
		$extension = array_pop($a);	//extract the extension.
		$ex=strtolower($extension);

		$extensionTypes = array(
		    'htm' => 'text/html',
		    'html' => 'text/html',
		    'txt' => 'text/plain',
		    'asc' => 'text/plain',
		    'bmp' => 'image/bmp',
		    'gif' => 'image/gif',
		    'jpeg' => 'image/jpeg',
		    'jpg' => 'image/jpeg',
		    'jpe' => 'image/jpeg',
		    'png' => 'image/png',
		    'ico' => 'image/vnd.microsoft.icon',
		    'mpeg' => 'video/mpeg',
		    'mpg' => 'video/mpeg',
		    'mpe' => 'video/mpeg',
		    'qt' => 'video/quicktime',
		    'mov' => 'video/quicktime',
		    'avi' => 'video/x-msvideo',
		    'wmv' => 'video/x-ms-wmv',
		    'mp2' => 'audio/mpeg',
		    'mp3' => 'audio/mpeg',
		    'rm' => 'audio/x-pn-realaudio',
		    'ram' => 'audio/x-pn-realaudio',
		    'rpm' => 'audio/x-pn-realaudio-plugin',
		    'ra' => 'audio/x-realaudio',
		    'wav' => 'audio/x-wav',
		    'css' => 'text/css',
		    'zip' => 'application/zip',
		    'pdf' => 'application/pdf',
		    'doc' => 'application/msword',
		    'bin' => 'application/octet-stream',
		    'exe' => 'application/octet-stream',
		    'class' => 'application/octet-stream',
		    'dll' => 'application/octet-stream',
		    'xls' => 'application/vnd.ms-excel',
		    'ppt' => 'application/vnd.ms-powerpoint',
		    'wbxml' => 'application/vnd.wap.wbxml',
		    'wmlc' => 'application/vnd.wap.wmlc',
		    'wmlsc' => 'application/vnd.wap.wmlscriptc',
		    'dvi' => 'application/x-dvi',
		    'spl' => 'application/x-futuresplash',
		    'gtar' => 'application/x-gtar',
		    'gzip' => 'application/x-gzip',
		    'js' => 'text/javascript',
		    'swf' => 'application/x-shockwave-flash',
		    'tar' => 'application/x-tar',
		    'xhtml' => 'application/xhtml+xml',
		    'au' => 'audio/basic',
		    'snd' => 'audio/basic',
		    'midi' => 'audio/midi',
		    'mid' => 'audio/midi',
		    'm3u' => 'audio/x-mpegurl',
		    'tiff' => 'image/tiff',
		    'tif' => 'image/tiff',
		    'rtf' => 'text/rtf',
		    'wml' => 'text/vnd.wap.wml',
		    'wmls' => 'text/vnd.wap.wmlscript',
		    'xsl' => 'text/xml',
		    'xml' => 'text/xml',
		    'php' => 'php',
		);

		if ( array_key_exists( $ex, $extensionTypes) )
			return $extensionTypes[$ex];
		else
			return 'application/octet-stream'; //Download if not known
	}



	/**
	 * To determine if a file has been modified since the last user visit.
	 * @param String $File
	 * @param boolean $SendHeader
	 * @return boolean
	 * @throws InvalidFileModifiedDateException
	 */
	protected static function IsModifiedSince($File,$SendHeader=true)
	{
		$lastModified = filemtime($File);	//get the time when the file was last modified.

		if ($lastModified === FALSE)	//If it cannot be determined, then throw an exception.
			throw new InvalidFileModifiedDateException("ERROR: The last modified date of the file: {$File} cannot be determined.");

		$gmdate_mod = gmdate('D, d M Y H:i:s', $lastModified) . ' GMT';		//convert the filetime to proper format.

		//check if the client is enabled to send headers that specifies the file modified time since user last visited it. If not, set that environment.
		$cond = isset($_SERVER['http_if_modified_since']) ? $_SERVER['http_if_modified_since'] : getenv("http_if_modified_since");
		$if_modified_since = preg_replace('/;.*$/', '', $cond);

		//If our calculated last modified date and the date returned from the server are same, then the file has not changed.
		if ($if_modified_since == $gmdate_mod)
		{
		    if ($SendHeader)
			    header("HTTP/1.0 304 Not Modified");

		    return false;
		}

		//else the file has changed. Thus the header must be reset to reflect this new time and it must tell the user to update their copy.
		if ($SendHeader)
		{
			header("Last-Modified: $gmdate_mod"); //Set the time this resource is modified
			header("Cache-Control: must-revalidate"); //Browser should ask me for cache
		}

		return true;
	}



	/**
	 * To extract the range of the file specified in the header (Range) returned by the client.
	 * @return IntArray
	 */
	protected static function calculateHttpRange()
	{

		//Common example of "Range":      Range: bytes=500-999,1000-1999

		if(isset($_SERVER['HTTP_RANGE']))
		{
			list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);	//extract the first set of range from all other ranges.

			if ($size_unit == 'bytes')
			{
			    //multiple ranges could be specified at the same time, but for simplicity only serve the first range
			    //http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
			    list($range, $extra_ranges) = explode(',', $range_orig, 2);
			}
			else
			{
			    $range = '';
			}
		}
		else
		{
			$range = '';
		}

		$extremes = explode('-', $range);	//store the range in an array. e.g. $extremes would like this: $extremes = array(500, 599);

		return $extremes;
	}



	/**
	 * Function to read the file from $seek_start to $seek_end.
	 * @param String $File
	 * @param int $seek_start
	 * @param int $seek_end
	 * @param boolean $Resumable
	 * @return boolean
	 */
	protected static function downloadSpeed($File, $seek_start, $seek_end, $Resumable = FALSE)
	{
		$FileSize = filesize($File);

		//Apply Download Limit
		if (  (DownloadManager::$BandwidthLimitInitialSize > 0) && ($FileSize > DownloadManager::$BandwidthLimitInitialSize) )
		{
			$advanceBy = DownloadManager::$BandwidthLimitSpeed;	//only read 'n' bytes at a time, as specified by the "$BandwidthLimitSpeed"
		}
		else
		{
			$advanceBy = 1024*8;	//since no limit, read 1024*8 bytes at a time.
		}

		if ($Resumable)
		{
			$f = fopen($File, "rb");
			fseek($f, $seek_start);		//seek to the position from where the file needs to be read.

			set_time_limit(0);

			//read till you reach the $seek_end of the file.
			while (ftell($f) <= $seek_end)
			{
				$currentPos = ftell($f);

				if ( ($advanceBy + $currentPos) > $seek_end )	//check if you have reached the position where you have to stop reading.
					$advanceBy = $seek_end - $currentPos;

                                    echof(fread($f, $advanceBy));	//read the specified number of bytes.

				fseek($f, $advanceBy+1, SEEK_CUR);	//seek to the position till you have read.

				flush();
				ob_flush();
				sleep(1);
			}
		}
		else
		{
			readfile($File);	//If resume is not supported, then read the whole file at once.
		}

		return true;
	}



	/**
	 * Function to download a file to the user.
	 * @param String $RealFile
	 * @param String $OutputFile
	 * @return boolean
	 */
	public static function download($RealFile,$OutputFile=null)
	{
		$File = realpath($RealFile);	//get the file path of the real file.

		if (!$File)	//If the real file does not exists, then just return.
			return false;

		if (!DownloadManager::IsModifiedSince($File))	//If the file has not modifed since last visit, no need to send data.
			return true;

		$FileSize = filesize($File);

		if ($OutputFile === null)	//If no outfile has not been mentioned, use the real file name by default.
			$OutputFile = basename($RealFile);

		if (strpos($OutputFile," ") !== false)	//to handle situtations where the file contains space in its name.
			$OutputFile= "'{$OutputFile}'";

		header("Content-Type: " . DownloadManager::MIME($OutputFile));	//specify the header for the file type.
		header("Content-disposition: filename={$OutputFile}");	//specify the header for the file name.

		$extremes = DownloadManager::calculateHttpRange();	//calculate the range, if present, till the file needs to be read.

		//check for the present of ranges that has to be read.
		if (isset($extremes[0]))
			$seek_start = $extremes[0];

		if (isset($extremes[1]))
			$seek_end = $extremes[1];

		//set start and end based on range (if set), else set defaults
		//also check for invalid ranges.
		$seek_end = (empty($seek_end)) ? ($FileSize - 1) : min(abs(intval($seek_end)),($FileSize - 1));
		$seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);

		//add headers if resumable
		$Resumable=false;
		if ($seek_start > 0 || $seek_end < ($FileSize - 1))
		{
			$Resumable=true;
			header('HTTP/1.1 206 Partial Content');		//specify header that the data you are getting is partial data.
		}

		//specify headers for common information.
		header('Accept-Ranges: bytes');
		header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$FileSize);
		header('Content-Length: '.($seek_end - $seek_start + 1));

		return DownloadManager::downloadSpeed($File, $seek_start, $seek_end, $Resumable);
	}



	/**
	* Serve some data to the client
	* @param String $Data
	* @param String $OutputFilename
	* @return boolean
	*/
	public static function serveData($Data, $OutputFilename)
	{
		$Filename = $OutputFilename;

		header("Content-type: " . DownloadManager::MIME($Filename));	//get the file type.
		header('Content-disposition: attachment; filename=' . $Filename); //add attachment; here to force download
		header('Content-length: '. strlen($Data));	//specify the browser the length of data it must expect.

		echof($Data);
		flush();

		return true;
	}
}

?>
