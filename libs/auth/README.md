**Info:** Placeholder for authentication library, including password management and user management.


Advanced Password Management Library:
--------------------------------------------

**Stand Alone	:** NO<BR>
**Depends On	:** User<BR>
**Reason	:**
		  <BR>
		  The constructor needs userID. This can be provided by developer. So this is not the issue.<BR>
		  However, we also try to get the user object with the given password. This generates "WrongPasswordException" which is crucial for "Brute Force" function to work.<BR>
		  For this reason, we are depending on User class.
		  <BR>
**Resolve	:**
		  <BR>
		  To make this library truly independent, the developer needs to connect their version of User Management class to this library by:<BR>
		  1. Providing username in the $user argument. (This is easy).<BR>
		  2. Replacing the below line:<BR>
			$userObj = User::existingUserObject($user, $pass);<BR>
		     With some other line that can check the user given password and if the password is wrong, can generate "WrongPasswordException".
		  <BR>




User Management Library:
--------------------------------
**Stand Alone	:** NO<BR>
**Depends On	:** User<BR>
**Reason	:** This library is to manage users. Thus it is meant to depend on User class.<BR>
**Resolve	:** NONE. No need to resolve. This library is meant to ship with the User library.<BR>




User Library:
-----------------------
**Stand Alone	:** YES<BR>
**Depends On	:** N/A<BR>
**Reason	:** N/A<BR>
**Resolve	:** N/A<BR>