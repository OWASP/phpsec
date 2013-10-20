<?php
class DefaultController extends phpsec\framework\DefaultController
{
	function Handle($Request)
	{
		return require_once (__DIR__ . "/../view/default/404.php");
	}
}