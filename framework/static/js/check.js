//Here the function just alerts for testing purposes. In reality, please change the alert with nice looking dialog boxes
//and then redirect or reload the page to stop the page from submitting the data to the server

function check()
{
	var formName = arguments[0];

	for(var i = 1; i < arguments.length; i++)
	{
		if (arguments[i] === "checkForBlanks")
		{
			checkForBlanks(formName);
		}
		else if (arguments[i] === "checkForPasswordsMatch")
		{
			checkForPasswordsMatch(formName);
		}
	}
}

function checkForBlanks(formName)
{
	var allElements = document.forms[formName].getElementsByTagName("input");
	for(var i=0; i<allElements.length; i++)
	{
		if( (allElements[i].getAttribute("type") === "text") || (allElements[i].getAttribute("type") === "password") )
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

function checkForPasswordsMatch(formName)
{
	var allElements = document.forms[formName].getElementsByTagName("input");
	var password = "";

	for(var i=0; i<allElements.length; i++)
	{
		if( (allElements[i].getAttribute("type") === "password") && (allElements[i].name.substring(0, 3) !== "_x_") )
		{
			if (password === "")
			{
				password = allElements[i].value;
			}
			else
			{
				if (password !== allElements[i].value)
				{
					alert("Passwords do not match!");
					return false;
				}
			}
		}
	}

	return true;
}


function verifyEmail(elementID)
{
	var value = document.getElementById(elementID).value;

	if (value.match(/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@([a-z0-9-])+\.([a-z0-9-]+)(\.[a-z]{2,63})?$/g) != value)
	{
		alert("Invalid Email!");
		return false;
	}

	return true;
}