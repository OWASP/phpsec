<?php

	namespace phpsec;

	require (__DIR__ . "/../model.php");

	/**
	 * PDO_SQLite wrapper class.
	 * Extends the parent DatabaseModel class.
	 */

	class Database_pdo_sqlite extends DatabaseModel {

		public function __construct () {
			$args = func_get_args ();
			if (isset($args[0]) && get_class($args[0]) === "PDO")
				$this->dbh = $args[0];
			else {
				$dbConfig = new DatabaseConfig ('pdo_sqlite',NULL,NULL,NULL);
				parent::__construct ($dbConfig);
				try {
					$this->dbh = new \PDO ("sqlite::memory:");
				}
				catch (PDOException $e) {
					echo $e->getMessage();
					return false;
				}
			}
			$this->dbh->setAttribute (\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
		}

		public function __destruct () {
			if (isset($this->dbh)) {
				$this->dbh = NULL;
			}
		}

		function prepare ($query) {
			return new DatabaseStatement_pdo_sqlite ($this, $query);
		}

	}

	/**
	 * PDO_SQLite prepared statements class.
	 * Extends the parent DatabaseStatementModel class.
	 */

	class DatabaseStatement_pdo_sqlite extends DatabaseStatementModel {

		public function __construct (Database_pdo_sqlite $db, $query) {
			parent::__construct ($db, $query);
		}

	}

?>