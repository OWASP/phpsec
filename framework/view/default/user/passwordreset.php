<?php

?>
<html>
	<head>
		<title>RNJ: Password Reset</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link rel="stylesheet" type="text/css" <?php echo('href="' . "http://localhost/rnj/framework/file/css/style.css" . '"'); ?> />
	</head>

	<body>
		<?php include (__DIR__ . "/../include.php"); ?>

		<div name="password-reset-div" id="password-reset-div">
			<form method="POST" action="" name="password-reset-form" id="password-reset-form" onsubmit="return check('password-reset-form', 'checkForBlanks', 'checkForPasswordsMatch');">
				<table name="password-reset-table" id="password-reset-table">
					<tr name="oldpass-field" id="oldpass-field">
						<td><label>Old Password:</label></td>
						<td><input type="password" name="_x_oldpass" id="_x_oldpass" maxlength="32"></td>	<!-- _x_ prepended to the name of this field tells the js function inside folder ../js/check.js/checkForPasswordsMatch that this field must not be considered for password match. -->
					</tr>
					<tr name="pass-field" id="pass-field">
						<td><label>Desired Password:</label></td>
						<td><input type="password" name="pass" id="pass" maxlength="32"></td>
					</tr>
					<tr name="repass-field" id="repass-field">
						<td><label>Re-Type Password:</label></td>
						<td><input type="password" name="repass" id="repass" maxlength="32"></td>
					</tr>
					<tr name="checkout-field" id="checkout-field">
						<td><input type="submit" name="submit" id="submit" value="Submit"></td>
						<td><input type="reset" name="reset" id="reset" value="Reset"></td>
					</tr>
				</table>
			</form>
		</div>

		<script type="text/javascript" <?php echo('src="' . "http://localhost/rnj/framework/file/js/check.js" . '"'); ?> ></script>
	</body>
</html>