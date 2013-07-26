<?php
namespace phpsec;


/**
 *					******************NOTE******************
 * 
 * Template file.
 * -------------------
 * 
 * This is a user customizable file. This file lists the filds that the user wants in their logs. E.g they may want Log Messages, Date, FileLocation etc.
 * For each of the field they require, those fields must be listed here.
 * 
 * Note: Please note that the fields are listed here in the order of their priority. i.e. list those fields first which are necessary and are must to be provided. Although, a user may not provide any value to the logs which is OK - defaults will be set.
 * 
 * The reason they need to be in order is because, while calling the log() function, the user must pass all the data as they are listed here.
 * 
 * e.g.: 
 *	 //Correct. Notice that we have provided values in order.
 *	 log($message, $type, $priority); 
 *	 log($message, $type);
 *	 log($message);
 * 
 *	 //Incorrect because values are not provided in order. This will cause values to be exchanged with each other.
 *	 log($message, $priority, $type);
 *	 log($message, $datetime);
 */




abstract class Template
{
	
	
	/**
	 * This variable holds the template.
	 * @var Array	An array to hold the template.
	 */
	public $template = array(
	    "MESSAGE"	=> "",
	    "TYPE"	=> "",
	    "PRIORITY"	=> "",
	    "DATETIME"	=> "",
	    "FILENAME"	=> "",
	    "LINE"	=> "",
	);
	
	
	
	/**
	 * Function to set default values in the template.
	 */
	protected function setDefaults()
	{
		$backtrace = debug_backtrace();	//get backtrace to know which file called this function.
		
		$fileGeneratingLog = "";
		$lineGeneratingLog = "";
		foreach ($backtrace as $func)
		{
			if ( strpos($func['class'], 'Logger') )		//Array in backtrace that will contain the class "Logger" is the file that originally called this function.
			{
				$fileGeneratingLog = $func['file'];	//get the appropriate filename from the backtrace
				$lineGeneratingLog = $func['line'];	//get the appropriate line from the backtrace
				break;
			}
		}
		
		//set the default value.
		$this->template["FILENAME"] = $fileGeneratingLog;
		$this->template["LINE"] = $lineGeneratingLog;
		$this->template["TYPE"] = "ERROR";
		$this->template["PRIORITY"] = "NORMAL";
		$this->template["DATETIME"] = date("m-d-Y H:i:s", time());
	}
	
	
	/**
	 * Abstract function that must be implemented by each media class to log data.
	 */
	abstract public function log($args);
	
	
	/**
	 * Abstract function that must be implemented by each media class to manipulate template acording to their needs.
	 */
	abstract protected function changeTemplate($args);
}