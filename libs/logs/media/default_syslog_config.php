<?php
namespace phpsec;


/**
 * This file contains the configuration array to test the storage of logs in SYSLOG. This configutation array will contain all of the details required to store the logs in the SYSLOG.
 *
 * NOTE: THIS IS THE DEFAULT CONFIGURATION FILE.
 */
return array(
    "MEDIA"			=> "SYSLOG",			//Media denotes that the logs must be stored in SYSLOG.
    "PHP_OPENLOG_IDENT"		=> "",				//openlog() ident option prepends some message infront of the logs.
    "PHP_OPENLOG_OPTION"	=> LOG_PID | LOG_PERROR,	//For a list of these options see: http://php.net/manual/en/function.openlog.php
    "PHP_OPENLOG_FACILITY"	=> LOG_USER,			//For a list of these options see: http://php.net/manual/en/function.openlog.php
    "PHP_SYSLOG_PRIORITY"	=> LOG_WARNING,			//For a list of these options see: http://php.net/manual/en/function.syslog.php
);

?>