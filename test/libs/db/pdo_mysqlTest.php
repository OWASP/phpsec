<?php

namespace phpsec;

require_once(__DIR__ . '/../../../libs/db/adapter/pdo_mysql.php');

class Database_pdo_mysql_Test extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var Database_pdo_mysql
	 */
	private $database;

	private $DB_NAME = 'OWASP';
	private $DB_USER = 'travis';
	private $DB_PASS = '';

	public function setUp()
	{
		$this->database = new Database_pdo_mysql ($this->DB_NAME, $this->DB_USER, $this->DB_PASS);
		$this->database->SQL(
			"CREATE TABLE IF NOT EXISTS `TEST` (
			  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `FIRST_NAME` varchar(20) DEFAULT NULL,
			  `LAST_NAME` varchar(20) DEFAULT NULL,
			  PRIMARY KEY (`ID`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;"
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
