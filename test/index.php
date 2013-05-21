<?php
namespace phpsec;

echo "<pre>";

ini_set('display_errors', 'On');

require_once ('../libs/db/adapter/pdo_mysql.php');
require_once ('../libs/core/Rand.class.php');	//at later time you won't need this.
require_once ('../libs/core/Exception.class.php');
require_once ('../libs/auth/User.class.php');
require_once ('/var/www/phpsec/libs/session/Session.class.php');	//STRANGE...RELATIVE PATH DOESN'T WORK HERE. HOWEVER ABSOLUTE PATH WORKS. WHY??????


Time::$realTime = true;
Time::resetTime();

$conn = null;
try
{
	$conn = new \phpsec\Database_pdo_mysql ('OWASP', 'root', 'testing');
}
catch (\Exception $e)
{
	echo $e->getMessage();
}

$user1 = null;
$user2 = null;
try
{
	$user1 = new User($conn, \phpsec\Rand::generateRandom(10));
	$user2 = new User($conn, \phpsec\Rand::generateRandom(10));
}
catch(\Exception $e)
{
	echo $e->getMessage();
}

try
{
	$session = array();
	
	$session[0] = new Session($user1, $conn);
	$session[1] = new Session($user1, $conn);
	$session[2] = new Session($user2, $conn);
	
	//this shows that if value of same key is changed from several concurrent sessions, only once copy is changed.
	$session[0] -> setData("lang1", "eng");
	$session[0] -> setData("lang1", "hebrew");
	$session[1] -> setData("lang1", "japanese");//since this session also belongs to the same user, it can change the data.
	$session[1] -> setData("charset1", "UTF-8");

	$session[1] -> setData("lang2", "hindi");
	$session[1] -> setData("charset2", "ASCII");

	$session[2] -> setData("lang3", "chinese");
	$session[2] -> setData("charset3", "CHINESE LANG");
	
	
	

	print_r ($session[0] ->getData("lang2")); //not accessible even though same user
	print_r ($session[0] ->getData("lang1")); //is accessible.
	print_r ($session[0] ->getData("lang123")); //this key does not exists
	
	print_r ($session[2] ->getData("charset3")); //it is accessible
	print_r ($session[2] ->getData("charset1")); //not accessible because different user. Hence empty array returned.
	
	
	if( $session[0] ->checkHTTPS() )
		echo "You have a HTTPS Connection.<BR>";
	else
		echo "You DO NOT have a HTTPS Connection.<BR>";
	
	Session::setInactivityTime(20);
	Session::setExpireTime(11);
	
	print_r($session[2] ->getAllSessions());
	$session[2] -> setData("honey", "bunny");
	sleep(3);
	print_r ($session[2] ->getData("honey"));
	print_r($session[2] ->getAllSessions());
	
	echo "*******************************<BR>";
	
	print_r($session[1] ->getAllSessions());
	$session[1] ->rollSession();
	print_r($session[1] ->getAllSessions());

	echo "---------------------------<BR>";
	
	sleep(3);
	$session[0] ->refreshSession();
	print_r($session[0] ->getAllSessions());
}
catch(\Exception $e)
{
	echo $e->getLine();
	echo $e -> getMessage();
}

?>