<?php

class TempPassController extends phpsec\framework\DefaultController
{
	protected $userID = NULL;
	protected $email = NULL;
	
	function __construct($userID, $email)
	{
		$this->userID = $userID;
		$this->email = $email;
	}
	
	function Handle()
	{
		try
		{
			if ( (isset($_GET['validate'])) && ($_GET['validate'] != "") )
			{
				if (phpsec\AdvancedPasswordManagement::tempPassword($this->userID, $_GET['validate']))
				{
					//what to do after validation is done.
				}
				else
				{
					$resendLink = \phpsec\HttpRequest::Root() . "/view/default/user/temppass.php";
					$this->error = "ERROR: This validation token does not match our records. Please insert the token that you received in your mail or click this <a href='{$resendLink}'>link to re-send a new token to your mail.</a>";
					//call the appropriate view to reload the page so that the user can enter correct token.
				}
			}
			else
			{
				$tempPass = phpsec\AdvancedPasswordManagement::tempPassword($this->userID);
			
				$message = "Please open the following link in order to complete the process:\n";


				//I am not sure about this link...so check this link again later.
				$message .= \phpsec\HttpRequest::Root() . "/view/default/user/temppass.php?validate=" . $tempPass . "\n\n\n";


				$message .= "If you did nothing to get this email, just ignore it.";
				$message = wordwrap($message, 70, "\r\n");

				$send = mail(	$this->email,
						"Authentication Email",
						$message,
						"FROM: " . "admin@" . \phpsec\HttpRequest::Host() . "\r\n"
					    );

				if ( !$send )
				{
					throw new phpsec\MailNotSendException("ERROR: Mail was not send!");
				}

				//call the "temppass.php" view in order to show the page where the user can enter the validation token. Or they can click the mail.
			}
		}
		catch (Exception $e)
		{
			$this->error = $e->getMessage();
			//call appropriate view.
			
			//You can also call here individual errors such as UserExistsException for a more precise action.
		}
	}
}