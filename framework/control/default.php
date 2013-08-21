<?php
class DefaultController extends phpsec\framework\DefaultController
{
	function Handle($Request)
	{
		require_once __DIR__."/../../tools/scanner.php";
		var_dump(phpsec\Scanner::scanFile(__DIR__."/../control/default.php"));
// 		print_r(phpsec\Scanner::scanDir(__DIR__."/../"));
		return true;
	}
	
	function unsafe()
	{
		$x="<p>yo</p>";
		echo "this should be just warning"; //safe stuff
		echo "this one {$x} is error";
		print "this is ". $x." unsafe too.";
		printf("warning here");
		vprintf("warn %s",array($x));
		vprintf("not ok ".$x." %s",array($x));
	}
}