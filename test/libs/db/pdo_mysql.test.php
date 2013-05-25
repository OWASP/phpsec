<?php

	namespace phpsec;

	require_once (__DIR__ . '/../../../libs/db/adapter/pdo_mysql.php');

	class Database_pdo_mysql_Test extends \PHPUnit_Framework_TestCase {

		private $DB_NAME = 'owasp',
				$DB_USER = 'root',
				$DB_PASS = '';

		public function setUp() {
			$this->pdo = new \phpsec\Database_pdo_mysql ($this->DB_NAME, $this->DB_USER, $this->DB_PASS);
			$this->pdo->SQL(
				"CREATE TABLE IF NOT EXISTS `TEST` (
				  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `FIRST_NAME` varchar(20) DEFAULT NULL,
				  `LAST_NAME` varchar(20) DEFAULT NULL,
				  PRIMARY KEY (`ID`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;"
			);
		}

		public function testDatabaseConnection() {
			$this->assertInstanceOf('PDO', $this->pdo->dbh, 'DB handler is not an instance of class PDO.');
		}

		public function testQueryExecutionWithExpandedListParameters() {
			$result = $this->pdo->SQL("INSERT INTO `TEST` (`FIRST_NAME`,`LAST_NAME`) VALUES (?,?)", 'Abhishek', 'Das');
			$this->assertInternalType('string', $result, 'Row insertion failed!');			
		}

		public function testQueryExecutionWithArrayParameters() {
			$result = $this->pdo->SQL("INSERT INTO `TEST` (`FIRST_NAME`,`LAST_NAME`) VALUES (?,?)", array('Rahul', 'Chaudhary'));
			$this->assertInternalType('string', $result, 'Row insertion failed!');			
		}

		public function testQueryExecutionWithNamedParameters() {
			$result = $this->pdo->SQL("INSERT INTO `TEST` (`FIRST_NAME`,`LAST_NAME`) VALUES (:first,:last)", array(':first' => 'Abbas', ':last' => 'Naderi'));
			$this->assertInternalType('string', $result, 'Row insertion failed!');			
		}

		public function tearDown() {
			$this->pdo->SQL("DROP TABLE `TEST`");
			$this->pdo = NULL;
		}

	}

?>