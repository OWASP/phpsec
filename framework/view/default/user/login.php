<?php

?>
<html>
	<head>
		<title>RNJ: Login</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link rel="stylesheet" type="text/css" <?php echo('href="' . "http://localhost/rnj/framework/file/css/style.css" . '"'); ?> />
	</head>

	<body>
		<?php include (__DIR__ . "/../../default/include.php"); ?>

		<div name="login-div" id="login-div">
			<form method="POST" action="" name="login-form" id="login-form" onsubmit="return check('login-form', 'checkForBlanks');">
				<table name="login-table" id="login-table">
					<tr name="user-field" id="user-field">
						<td><label>Username:</label></td>
						<td><input type="text" name="user" id="user" maxlength="32"></td>
					</tr>
					<tr name="pass-field" id="pass-field">
						<td><label>Password:</label></td>
						<td><input type="password" name="pass" id="pass" maxlength="32"></td>
					</tr>
					<tr name="remember-me-field" id="remember-me-field">
						<td><label>Remember Me:</label></td>
						<td><input type="checkbox" name="remember-me" id="remember-me"></td>
					</tr>
					<tr name="checkout-field" id="checkout-field">
						<td><input type="submit" name="submit" id="submit" value="Submit"></td>
						<td><input type="reset" name="reset" id="reset" value="Reset"></td>
					</tr>
				</table>
			</form>
		</div>

		<BR><a <?php $forgotpasswordURL = \phpsec\HttpRequest::Protocol() . "://" . \phpsec\HttpRequest::Host() . \phpsec\HttpRequest::PortReadable() . "/rnj/framework/forgotpassword"; echo "href='{$forgotpasswordURL}'"; ?> >Forgot Password</a> Click Here to recover your access to account in case you have forgot your password.

		<script type="text/javascript" <?php echo('src="' . "http://localhost/rnj/framework/file/js/check.js" . '"'); ?> ></script>
	</body>
</html>
