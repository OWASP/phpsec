<?php
namespace phpsec;



/**
 * This file contains the configuration Array to test the storage of logs in mails. This configutation Array will contain all of the details required to store the logs in the mail.
 * 
 * NOTE: THIS IS THE DEFAULT CONFIGURATION FILE.
 */
return array(
    "MEDIA"	=> "MAIL",					//Media denotes that the logs must be stored in MAIL.
    "TO"	=> "rahul.chaudhary@owasp.org",			//To tells where to send the mail.
    "FROM"	=> "rahul300chaudhary400@gmail.com",		//From tells where the mail is coming from.
    "REPLYTO"	=> "rahul300chaudhary400@gmail.com",		//Reply-To tells what address to send reply to the sent mail.
    "SUBJECT"	=> "Error Detected.",				//Subject will be the subject line of the mail.
    "MESSAGE"	=> "This message gets prepended to the LOGS.",	//This message gets prepended to the Log that is sent.
);

?>