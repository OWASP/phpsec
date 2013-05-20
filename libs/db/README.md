#Database Wrapper Classes

##Usage

* Include the required database adapter wrapper class. For example, in case of PDO_MYSQL

		require ('phpsec/libs/db/adapter/pdo_mysql.php');

* Set up a database connection

		$a = new \phpsec\Database_pdo_mysql ('DATABASE_NAME', 'DATABASE_USER', 'DATABASE_PASSWORD');

* In case you already have a connection made, you can pass the object directly to the constructor

		$pdo = new \PDO ("mysql:dbname=DATABASE_NAME;host=localhost;",'DATABASE_USER','DATABASE_PASSWORD');
		$a = new \phpsec\Database_pdo_mysql ($pdo);

* Execute queries. Parameters can be passed as arrays or expanded lists.

		$b = $a->SQL("SELECT * FROM users WHERE username = ? AND dob = ?", "abc", "09/10/1991");
		$b = $a->SQL("SELECT * FROM users WHERE username = ? AND dob = ?", array("abc","09/10/1991"));
		$b = $a->SQL("SELECT * FROM users WHERE username = :username AND dob = :dob", array(':username' => 'abc',':dob' => '09/10/1991'));

##Files

###model.php

####DatabaseConfig class

A single wrapper object for all database configuration options. Easier to pass around a single object to functions than an expanded list.

####DatabaseModel class

Parent class for all database wrapper classes. Provides most of the PDO compatible interface functions.

####DatabaseStatementModel class

Parent class for all database prepared statements. Contains methods to actually perform queries and fetch data.

###adapter/pdo_mysql.php

####Database_pdo_mysql class

PDO_MySQL wrapper class. Extends the DatabaseModel class.

####DatabaseStatement_pdo_mysql

PDO_MySQL prepared statement wrapper class. Extends the DatabaseStatementModel class.

###adapter/pdo_pgsql.php

####Database_pdo_pgsql class

PDO_PostgreSQL wrapper class. Extends the DatabaseModel class.

####DatabaseStatement_pdo_pgsql

PDO_PostgreSQL prepared statement wrapper class. Extends the DatabaseStatementModel class.

###adapter/pdo_sqlite.php

####Database_pdo_sqlite class

PDO_SQLite wrapper class. Extends the DatabaseModel class.

####DatabaseStatement_pdo_sqlite

PDO_SQLite prepared statement wrapper class. Extends the DatabaseStatementModel class.

###db.php

####DatabaseManager class

This is not being used at the moment. It might be needed later for when the set of libraries would be incorporated in a framework. Leaving it for legacy purposes.