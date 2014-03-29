<?php

?>
<html>
	<head>
		<title>RNJ: Home</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link rel="stylesheet" type="text/css" <?php echo('href="' . "http://localhost/rnj/framework/file/css/style.css" . '"'); ?> />
	</head>

	<body>
		<?php include (__DIR__ . "/../include.php"); ?>
		Hello, <?php echo $userID; ?>.<BR>
		This is the index page of the application. Once the user is logged in, this page is shown

		Click <a <?php $logoutURL = \phpsec\HttpRequest::Protocol() . "://" . \phpsec\HttpRequest::Host() . \phpsec\HttpRequest::PortReadable() . "/rnj/framework/logout"; echo "href='{$logoutURL}'"; ?> >here</a> to logout.
		<BR><BR><BR>
		Click <a <?php $passresetURL = \phpsec\HttpRequest::Protocol() . "://" . \phpsec\HttpRequest::Host() . \phpsec\HttpRequest::PortReadable() . "/rnj/framework/passwordreset"; echo "href='{$passresetURL}'"; ?> >here</a> to reset your password.
	</body>
</html>
