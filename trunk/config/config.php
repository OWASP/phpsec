<?php

	/**
	 * 
	 * If server configuration exists, use that.
	 * Otherwise use developer configuration options
	 * 
	 */

	if(file_exists(__DIR__.'/server.php'))
		return include('server.php');
	else
		return include('developer.php');

?>