<?php
namespace phpsec;

class DownloadManagerException extends \Exception {}
class InvalidFileModifiedDateException extends DownloadManagerException {}

class DownloadManager
{
	// taken from jFramework.
	public static function MIME ($File)
	{
		$a=explode('.', $File);
		$extension = array_pop($a);
		$ex=strtolower($extension);
		
		if ($ex=='htm') return'text/html';
		elseif ($ex=='html') return'text/html';
		elseif ($ex=='txt') return'text/plain';
		elseif ($ex=='asc') return'text/plain';
		elseif ($ex=='bmp') return'image/bmp';
		elseif ($ex=='gif') return'image/gif';
		elseif ($ex=='jpeg') return'image/jpeg';
		elseif ($ex=='jpg') return'image/jpeg';
		elseif ($ex=='jpe') return'image/jpeg';
		elseif ($ex=='png') return'image/png';
		elseif ($ex=='ico') return'image/vnd.microsoft.icon';
		elseif ($ex=='mpeg') return'video/mpeg';
		elseif ($ex=='mpg') return'video/mpeg';
		elseif ($ex=='mpe') return'video/mpeg';
		elseif ($ex=='qt') return'video/quicktime';
		elseif ($ex=='mov') return'video/quicktime';
		elseif ($ex=='avi') return'video/x-msvideo';
		elseif ($ex=='wmv') return'video/x-ms-wmv';
		elseif ($ex=='mp2') return'audio/mpeg';
		elseif ($ex=='mp3') return'audio/mpeg';
		elseif ($ex=='rm') return'audio/x-pn-realaudio';
		elseif ($ex=='ram') return'audio/x-pn-realaudio';
		elseif ($ex=='rpm') return'audio/x-pn-realaudio-plugin';
		elseif ($ex=='ra') return'audio/x-realaudio';
		elseif ($ex=='wav') return'audio/x-wav';
		elseif ($ex=='css') return'text/css';
		elseif ($ex=='zip') return'application/zip';
		elseif ($ex=='pdf') return'application/pdf';
		elseif ($ex=='doc') return'application/msword';
		elseif ($ex=='bin') return'application/octet-stream';
		elseif ($ex=='exe') return'application/octet-stream';
		elseif ($ex=='class') return'application/octet-stream';
		elseif ($ex=='dll') return'application/octet-stream';
		elseif ($ex=='xls') return'application/vnd.ms-excel';
		elseif ($ex=='ppt') return'application/vnd.ms-powerpoint';
		elseif ($ex=='wbxml') return'application/vnd.wap.wbxml';
		elseif ($ex=='wmlc') return'application/vnd.wap.wmlc';
		elseif ($ex=='wmlsc') return'application/vnd.wap.wmlscriptc';
		elseif ($ex=='dvi') return'application/x-dvi';
		elseif ($ex=='spl') return'application/x-futuresplash';
		elseif ($ex=='gtar') return'application/x-gtar';
		elseif ($ex=='gzip') return'application/x-gzip';
		elseif ($ex=='js') return'text/javascript';
		elseif ($ex=='swf') return'application/x-shockwave-flash';
		elseif ($ex=='tar') return'application/x-tar';
		elseif ($ex=='xhtml') return'application/xhtml+xml';
		elseif ($ex=='au') return'audio/basic';
		elseif ($ex=='snd') return'audio/basic';
		elseif ($ex=='midi') return'audio/midi';
		elseif ($ex=='mid') return'audio/midi';
		elseif ($ex=='m3u') return'audio/x-mpegurl';
		elseif ($ex=='tiff') return'image/tiff';
		elseif ($ex=='tif') return'image/tiff';
		elseif ($ex=='rtf') return'text/rtf';
		elseif ($ex=='wml') return'text/vnd.wap.wml';
		elseif ($ex=='wmls') return'text/vnd.wap.wmlscript';
		elseif ($ex=='xsl') return'text/xml';
		elseif ($ex=='xml') return'text/xml';
		else return 'application/octet-stream'; //Download if not known
	}
	
	public static function IsModifiedSince($File,$SendHeader=true)
	{
		$lastModified = filemtime($File);
		
		if ($lastModified === FALSE)
			throw new InvalidFileModifiedDateException("<BR>ERROR: The last modified date of the file: {$File} cannot be determined.<BR>");

		$gmdate_mod = gmdate('D, d M Y H:i:s', $lastModified) . ' GMT';
			
		$cond = isset($_SERVER['http_if_modified_since']) ? $_SERVER['http_if_modified_since'] : getenv("http_if_modified_since");
		$if_modified_since = preg_replace('/;.*$/', '', $cond);
		
		if ($if_modified_since == $gmdate_mod)
		{
		    if ($SendHeader)
			    header("HTTP/1.0 304 Not Modified");
		    
		    return false;
		}
		
		if ($SendHeader)
		{
			header("Last-Modified: $gmdate_mod"); //Set the time this resource is modified
			header("Cache-Control: must-revalidate"); //Browser should ask me for cache
		}
		
		return true;
	}
}

?>