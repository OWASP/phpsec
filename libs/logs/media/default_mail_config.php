<?php
namespace phpsec;



/**
 * This file contains the configuration Array to test the storage of logs in mails. This configutation Array will contain all of the details required to store the logs in the mail.
 *
 * NOTE: THIS IS THE DEFAULT CONFIGURATION FILE.
 */
return array(
    "MEDIA"	=> "MAIL",									//Media denotes that the logs must be stored in MAIL.
    "TO"	=> "rahul.chaudhary@owasp.org, r4hul.chaudhary@yahoo.com",			//To tells where to send the mail.
    "FROM"	=> "rahul300chaudhary400@gmail.com",						//From tells where the mail is coming from.
    "REPLYTO"	=> "rahul300chaudhary400@gmail.com",						//Reply-To tells what address to send reply to the sent mail.
    "CC"	=> "rac130@pitt.edu",								//CC tells where to carbon copy the mail
    "BCC"	=> "rahulchaudhary@acm.org",							//BCC tells where to blind carbon copy the mail
    "SUBJECT"	=> "Error Detected.",								//Subject will be the subject line of the mail.
    "MESSAGE"	=> wordwrap("This message gets prepended to the LOGS.", 70, "\r\n"),		//This message gets prepended to the Log that is sent. Message should always be word-wrapped.
    "OPTIONAL"	=> "MIME-Version: 1.0\r\nContent-Type: text/html; charset=ISO-8859-1\r\n"	//You can set any other optional parameters through this option. Please use the appropriate style of header writing as shown.
);

?>