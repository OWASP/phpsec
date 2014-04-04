<?php

?>

<html>
	<head>
		<title>RNJ - Temporary Password</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link rel="stylesheet" type="text/css" <?php echo('href="' . "http://localhost/rnj/framework/file/css/style.css" . '"'); ?> />
	</head>

	<body>
		<?php include (__DIR__ . "/../../default/include.php"); ?>

		<div name="temp_pass-div" id="temp_pass-div">
		<p><h2>An e-mail has been sent to the email address you provided. Click on the link inside the email to complete this process.</h2></p>
		<p>If you have not received your email, please <a <?php $link = \phpsec\HttpRequest::Protocol() . "://" . \phpsec\HttpRequest::Host() . \phpsec\HttpRequest::PortReadable() . "/rnj/framework/temppass?user=" . $_GET['user'] . "&mode=" . $_GET['mode'] ."&email=" . $_GET['email']; echo("href='{$link}'"); ?> >click this link</a> to resend the mail.</p>
		</div>
	</body>
</html>