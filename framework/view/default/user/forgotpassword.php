<?php

?>

<div name="forgot-password-div" id="forgot-password-div">
	<form method="POST" action="" name="forgot-password-form" id="forgot-password-form" onsubmit="return check('forgot-password-form', 'checkForBlanks');">
		<table name="forgot-password-table" id="forgot-password-table">
			<tr name="user-field" id="user-field">
				<td><label>Username:</label></td>
				<td><input type="text" name="user" id="user" maxlength="32"></td>
			</tr>
			<tr name="checkout-field" id="checkout-field">
				<td><input type="submit" name="submit" id="submit" value="Submit"></td>
				<td><input type="reset" name="reset" id="reset" value="Reset"></td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript" src="../../js/check.js"></script>