<?php
namespace phpsec;

abstract class Template
{
	public $template = array(
	    "MESSAGE"	=> "",
	    "TYPE"	=> "",
	    "PRIORITY"	=> "",
	    "DATETIME"	=> "",
	    "FILENAME"	=> "",
	);
	
	protected function setDefaults()
	{
		$backtrace = debug_backtrace();
		
		$fileGeneratingLog = "";
		foreach ($backtrace as $func)
		{
			if ( strpos($func['class'], 'Logger') )
			{
				$fileGeneratingLog = $func['file'];
				break;
			}
		}
		
		$this->template["FILENAME"] = $fileGeneratingLog;
		$this->template["TYPE"] = "ERROR";
		$this->template["PRIORITY"] = "NORMAL";
		$this->template["DATETIME"] = date("m-d-Y H:i:s", time());
	}
	
	abstract public function log($args);
	
	abstract protected function changeTemplate($args);
}