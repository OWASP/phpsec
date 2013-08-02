<?php

namespace phpsec;

require_once(__DIR__ . '/../../../libs/db/adapter/pdo_sqlite.php');

class Database_pdo_sqlite_Test extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var Database_pdo_sqlite
	 */
	private $database;

	public function setUp()
	{
		$this->database = new Database_pdo_sqlite ();
		$this->database->SQL(
			"CREATE TABLE IF NOT EXISTS `TEST` (
			  ID INTEGER PRIMARY KEY,
			  `FIRST_NAME` TEXT,
			  `LAST_NAME` TEXT
			)"
		);
	}

	public function tearDown()
	{
		$this->database->SQL("DROP TABLE `TEST`");
		$this->database = NULL;
	}

	public function testDatabaseConnection()
	{
		$this->assertInstanceOf('PDO', $this->database->dbh, 'DB handler is not an instance of class PDO.');
	}

	public function testQueryExecutionWithExpandedListParameters()
	{
		$result = $this->database->SQL("INSERT INTO `TEST` (`FIRST_NAME`,`LAST_NAME`) VALUES (?,?)", 'Abhishek', 'Das');
		$this->assertInternalType('string', $result, 'Row insertion failed!');
	}

	public function testQueryExecutionWithArrayParameters()
	{
		$result = $this->database->SQL("INSERT INTO `TEST` (`FIRST_NAME`,`LAST_NAME`) VALUES (?,?)", array('Rahul', 'Chaudhary'));
		$this->assertInternalType('string', $result, 'Row insertion failed!');
	}

	public function testQueryExecutionWithNamedParameters()
	{
		$result = $this->database->SQL("INSERT INTO `TEST` (`FIRST_NAME`,`LAST_NAME`) VALUES (:first,:last)", array(':first' => 'Abbas', ':last' => 'Naderi'));
		$this->assertInternalType('string', $result, 'Row insertion failed!');
	}
}
