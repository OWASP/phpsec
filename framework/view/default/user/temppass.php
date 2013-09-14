<?php

?>

<div name="temp-pass-div" id="temp-pass-div">
	<h4>Please check your mail to get this validation token and paste it here, OR, click the link in your mail to complete the process.</h4><BR>
	
	<form method="GET" action="" name="temp-pass-form" id="temp-pass-form" onsubmit="return check('temp-pass-form', 'checkForBlanks');">
		<table name="temp-pass-table" id="temp-pass-table">
			<tr name="temp-pass-field" id="temp-pass-field">
				<td><label>Verification Token:</label></td>
				<td><input type="text" name="validate" id="validate" maxlength="128"></td>
			</tr>
			<tr name="checkout-field" id="checkout-field">
				<td><input type="submit" name="submit" id="submit" value="Submit"></td>
				<td><input type="reset" name="reset" id="reset" value="Reset"></td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript" src="../../js/check.js"></script>