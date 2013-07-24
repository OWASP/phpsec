<?php
namespace phpsec;

abstract class Template
{
	public static $template = array(
	    "MESSAGE"	=> "",
	    "FILENAME"	=> "",
	    "TYPE"	=> "",
	    "PRIORITY"	=> "",
	    "DATETIME"	=> "",
	);
	
	protected function setDefaults()
	{
		Template::$template["TYPE"] = "ERROR";
		Template::$template["PRIORITY"] = "NORMAL";
		Template::$template["DATETIME"] = date("m-d-Y H:i:s", time());
	}
	
	abstract public function log($args);
	
	abstract protected function changeTemplate($args);
}