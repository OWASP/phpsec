<?php

class ForgotPasswordController extends phpsec\framework\DefaultController
{
	function Handle()
	{
		try
		{
			if ( isset($_POST['user']) && ($_POST['user'] != "") )
			{
				$email = phpsec\User::getPrimaryEmail($_POST['user']);
				
				if ($email === FALSE)
				{
					$this->error = "This email ID is not registered in our DB. Please enter the email you provided at the time of sign-up.";
					//call appropriate view to reload the page so that user can enter their correct email address.
				}
				
				
				
				
				
				
				
				
				
				//************************************************************************************PROBLEM HERE
				//call temp-pass controller to validate.
				$tempPass = new TempPassController($_POST['user'], $email);
				if ($tempPass->Handle())
				{
					$userObj = phpsec\UserManagement::forceLogIn($_POST['user']);
					
					//call appropriate view after the user object has been created.
				}
			}
			else
			{
				//call view to show the forgot-password page.
			}
		}
		catch (Exception $e)
		{
			$this->error = $e->getMessage();
			//call appropriate view.
			
			//You can also call here individual errors such as UserNotFoundException for a more precise action.
		}
	}
}