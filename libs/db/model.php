<?php

	/**
	 * DatabaseModel class.
	 * Intended as parent for all database wrapper classes.
	 */

	abstract class DatabaseModel {

		public $dbConfig;

		public $dbh;

		public function __construct (DatabaseConfig $dbConfig) {
			$this->dbConfig = $dbConfig;
		}

		abstract protected function prepare ($query);

		public function lastInsertId () {
			return $this->dbh->lastInsertId();
		}

		public function SQL ($query) {
			$args = func_get_args ();
			array_shift ($args);
			$statement = $this->prepare ($query);
			if (!empty ($args[0])) {
				if (is_array ($args[0]))
					$statement->execute ($args[0]);
				else if (count ($args) >= 1) {
					call_user_func_array (array ($statement, "bindAll"), $args);
					$statement->execute ();
				}
			}
			else
				$statement->execute ();
			$type = substr (trim (strtoupper ($query)), 0, 3);
			if ($type == "INS") {
				$res = $this->LastInsertId ();
				if ($res == 0)
					return $statement->rowCount ();
				return $res;
			}
			elseif ($type == "DEL" or $type == "UPD") {
				return $statement->rowCount ();
			}
			elseif ($type == "SEL") {
				return $statement->fetchAll();
			}
			else
				return null;
		}

		public function query ($query) {
			return $this->dbh->query ($query);
		}

		public function exec ($query) {
			return $this->dbh->exec ($query);
		}

	}

	/**
	 * DatabaseStatementModel class.
	 * Intended as parent for all database prepared statement classes.
	 */

	abstract class DatabaseStatementModel {

		protected $db;

		protected $query;

		protected $params;

		protected $statement;

		public function __construct ($db, $query) {
			$this->db = $db;
			$this->query = $query;
			$this->statement = $db->dbh->prepare ($query);
		}

		public function __destruct () {
			if (isset($this->statement)) {
				$this->db = NULL;
				$this->query = NULL;
				$this->params = NULL;
				$this->statement = NULL;
			}
		}

		public function fetch () {
			return $this->statement->fetch (\PDO::FETCH_ASSOC);
		}

		public function fetchAll() {
			return $this->statement->fetchAll (\PDO::FETCH_ASSOC);
		}

		public function bindAll () {
			$params = func_get_args ();
			$this->params = $params;
			$i = 0;
			foreach ($params as &$param)
				$this->statement->bindValue (++$i, $param);
		}

		public function execute () {
			$args = func_get_args ();
			if (!empty ($args[0]) && is_array ($args[0]))
				return $this->statement->execute ($args[0]);
			else
				return $this->statement->execute ();
		}

		public function rowCount () {
			return $this->statement->rowCount ();
		}

	}

?>