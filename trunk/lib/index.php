<?php

ini_set('display_errors', 'On');

require('Session.class.php');

try
{
	$user1 = new User();
	$user2 = new User();
	
	$user1 -> setUserID(1234);
	$user2 -> setUserID(12);
	
	$connections = array();

	$connections[0] = new Session($user1, "mysql", "OWASP", "root", "testing");
	$connections[1] = new Session($user1, "mysql", "OWASP", "root", "testing");
	$connections['session_3'] = new Session($user2, "mysql", "OWASP", "root", "testing");

	//this shows that if value of same key is changed from several concurrent sessions, only once copy is changed.
	$connections[0] -> setData("lang1", "eng");
	$connections[0] -> setData("lang1", "hebrew");
	$connections[1] -> setData("lang1", "japanese");//since this session also belongs to the same user, it can change the data.
	$connections[1] -> setData("charset1", "UTF-8");

	$connections[1] -> setData("lang2", "hindi");
	$connections[1] -> setData("charset2", "ASCII");

	$connections['session_3'] -> setData("lang3", "chinese");
	$connections['session_3'] ->setData("charset3", "CHINESE LANG");

	echo "<pre>";

	print_r ($connections['session_3'] ->getData("charset3")); //it is accessible
	print_r ($connections['session_3'] ->getData("charset1")); //not accessible because different user. Hence empty array returned.
	print_r ($connections[0] ->getData("lang2")); //is accessible.
	print_r ($connections[0] ->getData("lang1")); //is accessible.
	print_r ($connections[0] ->getData("lang123")); //this key does not exists
	
	$connections[0] ->refreshSession();
	
	$connections['session_3'] ->destroySession();
}
catch(Exception $e)
{
	echo $e -> getMessage();
}

?>