# Secure Database Library

## Introduction

This library is compatible with PHP PDO, but doesn't allow insecure operations (such as concatenation of values in any form) with it. We have enforced prepared statements for all data that is to be sent to the database engine, and will enforce whitelisting (via taint tracking) of all SQL parameters (such as limit and order by) where prepared data are not supported by the back-end engine. A base library provides all these features abstracted from database engines, and derived libraries for each common database engine.

## Usage

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

## Files

### adapter/base.php

#### DatabaseConfig class

A single wrapper object for all database configuration options. Easier to pass around a single object to functions than an expanded list.

#### DatabaseModel class

Parent class for all database wrapper classes. Provides most of the PDO compatible interface functions.

#### DatabaseStatementModel class

Parent class for all database prepared statements. Contains methods to actually perform queries and fetch data.

### adapter/pdo_mysql.php

#### Database\_pdo\_mysql class

PDO_MySQL wrapper class. Extends the DatabaseModel class.

#### DatabaseStatement\_pdo\_mysql

PDO_MySQL prepared statement wrapper class. Extends the DatabaseStatementModel class.

### adapter/pdo_pgsql.php

#### Database\_pdo\_pgsql class

PDO_PostgreSQL wrapper class. Extends the DatabaseModel class.

#### DatabaseStatement\_pdo\_pgsql

PDO_PostgreSQL prepared statement wrapper class. Extends the DatabaseStatementModel class.

### adapter/pdo_sqlite.php

#### Database\_pdo\_sqlite class

PDO_SQLite wrapper class. Extends the DatabaseModel class.

#### DatabaseStatement\_pdo\_sqlite

PDO_SQLite prepared statement wrapper class. Extends the DatabaseStatementModel class.

### dbmanager.php

#### DatabaseManager class

This is not being used at the moment. It might be needed later for when the set of libraries would be incorporated in a framework. Leaving it for legacy purposes.