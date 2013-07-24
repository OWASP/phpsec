<?php
namespace phpsec;

abstract class Template
{
	public $template = array(
	    "MESSAGE"	=> "",
	    "FILENAME"	=> "",
	    "TYPE"	=> "",
	    "PRIORITY"	=> "",
	    "DATETIME"	=> "",
	);
	
	protected function setDefaults()
	{
		$this->template["TYPE"] = "ERROR";
		$this->template["PRIORITY"] = "NORMAL";
		$this->template["DATETIME"] = date("m-d-Y H:i:s", time());
	}
	
	abstract public function log($args);
	
	abstract protected function changeTemplate($args);
}