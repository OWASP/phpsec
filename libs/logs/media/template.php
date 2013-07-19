<?php
namespace phpsec;

abstract class Template
{
	public $template = array(
	    "TYPE"	=> "",
	    "PRIORITY"	=> "",
	    "DATETIME"	=> "",
	    "FILENAME"	=> "",
	    "MESSAGE"	=> "",
	);
	
	abstract public function log($message);
	
	abstract public function changeFormatToTemplate($message);
}