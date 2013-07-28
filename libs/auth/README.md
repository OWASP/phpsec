placeholder for authentication library, including password management and user management.


Advanced Password Management Library:
--------------------------------------------

Stand-Alone	: NO
Depends On	: User
Reason		: The constructor needs userID. This can be provided by developer. So this is not the issue.
		  However, we also try to get the user object with the given password. This generates "WrongPasswordException" which is crucial for "Brute Force" function to work.
		  For this reason, we are depending on User class.
Resolve		: To make this library truly independent, the developer needs to connect their version of User Management class to this library by:
		  1) Providing username in the $user argument. (This is easy).
		  2) Replacing the below line:
			$userObj = User::existingUserObject($user, $pass);
		     With some other line that can check the user given password and if the password is wrong, can generate "WrongPasswordException".