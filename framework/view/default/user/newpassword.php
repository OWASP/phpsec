<?php

?>
<html>
	<head>
		<title>RNJ: Request New Password</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link rel="stylesheet" type="text/css" <?php echo('href="' . "http://localhost/rnj/framework/file/css/style.css" . '"'); ?> />
	</head>

	<body>
		<?php include (__DIR__ . "/../include.php"); ?>

		<div name="new-password-div" id="new-password-div">
			<form method="POST" action="" name="new-password-form" id="new-password-form" onsubmit="return check('new-password-form', 'checkForBlanks', 'checkForPasswordsMatch');">
				<table name="new-password-table" id="new-password-table">
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