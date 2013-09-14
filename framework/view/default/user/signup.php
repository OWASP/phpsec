<?php

?>

<div name="signup-div" id="signup-div">
	<form method="POST" action="" name="signup-form" id="signup-form" onsubmit="return check('signup-form', 'checkForBlanks', 'checkForPasswordsMatch');">
		<table name="signup-table" id="signup-table">
			<tr name="user-field" id="user-field">
				<td><label>Desired Username:</label></td>
				<td><input type="text" name="user" id="user" maxlength="32"></td>
			</tr>
			<tr name="email-field" id="email-field">
				<td><label>E-Mail:</label></td>
				<td><input type="text" name="email" id="email" maxlength="128"></td>
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

<script type="text/javascript" src="../../js/check.js"></script>