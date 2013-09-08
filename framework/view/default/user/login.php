<?php

?>

<div name="login-div" id="login-div">
	<form method="POST" action="" name="login-form" id="login-form" onsubmit="return check();">
		<table name="login-table" id="login-table">
			<tr name="user-field" id="user-field">
				<td><label>Username:</label></td>
				<td><input type="text" name="user" id="user" maxlength="32"></td>
			</tr>
			<tr name="pass-field" id="pass-field">
				<td><label>Password:</label></td>
				<td><input type="text" name="pass" id="pass" maxlength="32"></td>
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

<script>
	function check()
	{
		checkForBlanks();
	}

	function checkForBlanks()
	{
		var allElements = document.forms['login-form'].getElementsByTagName("input");
		for(i=0; i<allElements.length; i++)
		{
			if(allElements[i].getAttribute("type") === "text")
			{
				if(allElements[i].value == "")
				{
					alert("Empty Fields are not allowed!");
					return false;
				}
			}
		}

		return true;
	}
</script>