<?php

	/**
	 * PDO_MySQL wrapper class.
	 * Extends the parent DatabaseModel class.
	 */

	class Database_pdo_mysql extends DatabaseModel {

		public function __construct (DatabaseConfig $dbConfig) {
			parent::__construct($dbConfig);
			if ($dbConfig->username !== "")
			{
				$this->dbh = new \PDO ("mysql:dbname={$dbConfig->dbname};host={$dbConfig->host};",$dbConfig->username,$dbConfig->password);
				$this->dbh->setAttribute (\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
			}
		}

		public function __destruct () {
			if (isset($this->dbh)) {
				$this->dbh = NULL;
			}
		}

		function prepare ($query) {
			return new DatabaseStatement_pdo_mysql ($this, $query);
		}

	}

	/**
	 * PDO_MySQL prepared statements class.
	 * Extends the parent DatabaseStatementModel class.
	 */

	class DatabaseStatement_pdo_mysql extends DatabaseStatementModel {

		public function __construct (Database_pdo_mysql $db, $query) {
			parent::__construct ($db, $query);
		}

	}

?>