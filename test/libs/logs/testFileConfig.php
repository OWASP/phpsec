<?php
namespace phpsec;

/**
 * This file contains the configuration Array to test the storage of logs in files. This configutation Array will contain all of the details required to store the logs in the file.
 *
 * Note: There is also a default configuration provided for ease of use. However, users can create their own configuration files similar to this or they can check the wiki on "how to create configuration files for logs".
 *
 * Look to the default configuration file or the wiki page to know the meaning of each field.
 */

return array(
	"FILENAME" => "myfile.php",
	"MEDIA" => "FILE",
	"MODE" => "a",
);
