<?php
namespace phpsec;


/**
 * This file contains the configuration array to test the storage of logs in DB. This configutation array will contain all of the details required to store the logs in the DB.
 * 
 * NOTE: THIS IS THE DEFAULT CONFIGURATION FILE.
 */
return array(
    "MEDIA"	=> "DBLogs",	//Media denotes that the logs must be stored in DB.
    "ADAPTER"	=> "pdo_mysql",	//Adapter denotes the type of DB.
    "DBNAME"	=> "OWASP",	//DBName denotes the name of the DB.
    "USERNAME"	=> "travis",	//Username denotes the username used to access the DB.
    "PASSWORD"	=> "",	//Password denotes the password used to access the DB.
    "TABLENAME"	=> "LOGS",	//TableName denotes the name of the table where the logs would be stored.
);
