<?php
namespace phpsec;


/**
 * This file contains the configuration array to test the storage of logs in DB. This configutation array will contain all of the details required to store the logs in the DB.
 * 
 * NOTE: THIS IS THE DEFAULT CONFIGURATION FILE.
 */
return array(
    "media"	=> "DBLogs",	//Media denotes that the logs must be stored in DB.
    "adapter"	=> "pdo_mysql",	//Adapter denotes the type of DB.
    "dbname"	=> "OWASP",	//DBName denotes the name of the DB.
    "username"	=> "root",	//Username denotes the username used to access the DB.
    "password"	=> "testing",	//Password denotes the password used to access the DB.
    "tablename"	=> "LOGS",	//TableName denotes the name of the table where the logs would be stored.
);

?>