<?php
namespace phpsec;



/**
 * This file contains the configuration Array to test the storage of logs in files. This configutation Array will contain all of the details required to store the logs in the file.
 *
 * NOTE: THIS IS THE DEFAULT CONFIGURATION FILE.
 */
return array(
    "FILENAME"	=> "logs.txt",	//FileName denotes the name of the file where the logs will be stored.
    "MEDIA"	=> "FILE",	//Media denotes that the logs must be stored in file.
    "MODE"	=> "a",		//Mode denotes the mode in which the files must be opened. For a full list of modes, see http://php.net/manual/en/function.fopen.php
);

?>