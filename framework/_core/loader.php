<?php
namespace phpsec\framework;
const whoami="phpsec framework 1.0";
/**
 * This file is in charge of setting some environment variables prior to running
 * the front controller, it tries to set those properly even in CLI mode using
 * some tricks.
 */

if (\phpsec\HttpRequest::isCLI()) 	//php-cli
{
	//the request should be provided in CLI as:
	#php front.php "folder/file?a=b&cd="
	if ( $argc==1)
		\phpsec\HttpRequest::SetBaseURL("http://localhost/");
	else
	{
		\phpsec\HttpRequest::SetBaseURL("http://localhost/".$argv[1]);
		if (strpos($argv[1],"?")!==false) //query string extraction
		{
			$QueryString=substr($argv[1],strpos($argv[1],"?")+1);
			$Params=explode("&",$QueryString);
			foreach ($Params as $p)
			{
				if (strpos($p,"=")===false)
				{
					$_GET[urldecode($p)]="";
					continue;
				}
				list($k,$v)=explode("=",$p);
				$_GET[urldecode($k)]=urldecode($v);
			}
		}

	}
}
else 								//php-cgi
{
	$InternalRequest=$_GET['___r'];
	unset($_GET['___r']);
	unset($_REQUEST['___r']);
	$URL=\phpsec\HttpRequest::URL(false);
	\phpsec\HttpRequest::SetBaseURL(substr($URL,0,strlen($URL)-strlen($InternalRequest)));
}

require_once __DIR__."/../config/routes.php";