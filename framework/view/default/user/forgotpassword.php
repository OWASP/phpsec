<?php

?>
<html>
	<head>
		<title>RNJ: Forgot Password</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link rel="stylesheet" type="text/css" <?php echo('href="' . "http://localhost/rnj/framework/file/css/style.css" . '"'); ?> />
	</head>

	<body>
		<?php include (__DIR__ . "/../include.php"); ?>

		<div name="forgot-password-div" id="forgot-password-div">
			<form method="POST" action="" name="forgot-password-form" id="forgot-password-form" onsubmit="return check('forgot-password-form', 'checkForBlanks');">
				<table name="forgot-password-table" id="forgot-password-table">
					<tr name="email-field" id="email-field">
						<td><label>Primary Email:</label></td>
						<td><input type="text" name="email" id="email" maxlength="128"></td>
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