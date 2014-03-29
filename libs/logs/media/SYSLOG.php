<?php
namespace phpsec;


/**
 * Required Files.
 */
require_once 'template.php';



class SYSLOG extends Template
{

	/**
	 * Variable to store the configuration file of the user.
	 * @var Array
	 */
	protected $syslogConfig = null;



	/**
	 * Constructor function to initiate the object of this class.
	 * @param array $config
	 */
	public function __construct($config)
	{
		$this->syslogConfig = $config;	//store the file configuration file.
	}



	/**
	 * Function to write the log messages to the syslog.
	 * @param Array $args	Array of messages as given by the user to be written in log files.
	 */
	public function log($args)
	{
		$message = $this->changeTemplate($args);	//change the user given message appropriate to the template of the log files. This is necessary to maintain consistency among all the log files.

		openlog($this->syslogConfig['PHP_OPENLOG_IDENT'], $this->syslogConfig['PHP_OPENLOG_OPTION'], $this->syslogConfig['PHP_OPENLOG_FACILITY']);	//open syslog with proper "OPTION" and "FACILITY"
		syslog($this->syslogConfig['PHP_SYSLOG_PRIORITY'], $message);		//Write to SYSLOG with proper "priority"
		closelog();		//close the SYSLOG after writing.
	}



	/**
	 * Function to change the user given message to the template defined by the user. This makes the log files consistent throughout the application.
	 * @param Array $args	Array of messages as given by the user to be written in log files.
	 * @return String
	 */
	protected function changeTemplate($args)
	{
		$this->setDefaults();	//set defaults in the log messages. Such as date.

		$i = 0;
		$message = "";

		$myTemplate = $this->template;	//copy the template. This is necessary because we do not want to change the original template.

		//for each value of the template.
		foreach(  $myTemplate as $value)
		{
			//check if a value is provided by the user for that entry in the template.
			if (isset($args[$i]) && $args[$i] !== "")
			{
				$value = $args[$i];	//if user has provided a value, then overwrite the default value with this user provided value.
			}

			//enclose each log message in the square brackets for proper distinction. Also add spaces and tabs the create gap between each log messages.
			$message = $message . "[" . $value . "]" . "\t\t";
			$i = $i+1;
		}

		//separate each log message with a new line.
		$message = $message . "\n";

		//return the message to be stored.
		return $message;
	}
}

?>