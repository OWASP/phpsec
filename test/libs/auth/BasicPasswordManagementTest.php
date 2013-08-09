<?php

namespace phpsec;

require_once __DIR__ . "/../../../libs/core/random.php";
require_once __DIR__ . "/../../../libs/auth/user.php";

class BasicPasswordManagementTest extends \PHPUnit_Framework_TestCase {
	/**
	 * To check if we can retrieve the static salt.
	 */
	public function testGetStaticSalt()
	{
		$this->assertTrue(strlen(BasicPasswordManagement::getStaticSalt()) > 1);
	}


	/**
	 * To check if we can get the entropy. This string will produce an entropy greater than 1.
	 */
	public function testEntropy()
	{
		$this->assertTrue(BasicPasswordManagement::Entropy("OWASP PHP") > 1);
	}


	/**
	 * To check if a string as ordered characters. (3 cases are checked).
	 */
	public function testHasOrderedCharacters()
	{
		$this->assertTrue(BasicPasswordManagement::hasOrderedCharacters("abcd", 3));
		$this->assertTrue(BasicPasswordManagement::hasOrderedCharacters("dcba", 3));

		$this->assertFalse(BasicPasswordManagement::hasOrderedCharacters("abed", 3));
	}


	/**
	 * To check if a string as keyboard ordered characters. (3 cases are checked).
	 */
	public function testHasKeyboardOrderedCharacters()
	{
		$this->assertTrue(BasicPasswordManagement::hasKeyboardOrderedCharacters("qwert", 3));
		$this->assertTrue(BasicPasswordManagement::hasKeyboardOrderedCharacters("trewq", 3));

		$this->assertFalse(BasicPasswordManagement::hasKeyboardOrderedCharacters("trwwQz", 3));
	}


	/**
	 * To check if the string is a phone number (3 cases are checked)
	 */
	public function testIsPhoneNumber()
	{
		$this->assertTrue(BasicPasswordManagement::isPhoneNumber("4125199634"));
		$this->assertTrue(BasicPasswordManagement::isPhoneNumber("+14125199634"));

		$this->assertFalse(BasicPasswordManagement::isPhoneNumber("412-519-9634"));
	}


	/**
	 * To check if the string contains a phone-pattern (3 cases are checked)
	 */
	public function testContainsPhoneNumber()
	{
		$this->assertTrue(BasicPasswordManagement::containsPhoneNumber("rash4125199634"));
		$this->assertTrue(BasicPasswordManagement::containsPhoneNumber("+14125199634rahul"));

		$this->assertFalse(BasicPasswordManagement::containsPhoneNumber("412-519-9634"));
	}


	public function provideValidDateStrings()
	{
		return array(
			"european english" => array("23-May 2012"),
			"american" => array("may/21-1990"),
			"iso caseinsensitive" => array("2021 FeB.13"),
		);
	}

	/**
	 * @dataProvider provideValidDateStrings
	 */
	public function testIsDateShouldReturnTrue($date)
	{
		$this->assertTrue(BasicPasswordManagement::isDate($date));
	}

	public function testIsDateShouldReturnFalse()
	{
		$this->assertFalse(BasicPasswordManagement::isDate("rash21-May-rash"));
	}

	/**
	 * To check if the string contains a date. (4 cases are checked)
	 */
	public function testContainsDate()
	{
		$this->assertTrue(BasicPasswordManagement::containsDate("ra23-May 2012aa"));
		$this->assertTrue(BasicPasswordManagement::containsDate("may/21-90aqw"));
		$this->assertTrue(BasicPasswordManagement::containsDate("qw21 FeB.13"));
		$this->assertTrue(BasicPasswordManagement::containsDate("23/01//13"));
	}


	/**
	 * To check if the string contains double words. (2 cases are checked)
	 */
	public function testContainsDoubledWords()
	{
		$this->assertTrue(BasicPasswordManagement::containDoubledWords("dogdog"));
		$this->assertTrue(BasicPasswordManagement::containDoubledWords("dogdogs"));
	}


	/**
	 * To check if a string contains another string. (2 cases are checked)
	 */
	public function testContainsString()
	{
		$this->assertTrue(BasicPasswordManagement::containsString("this is a sTring", "sTRinG"));
		$this->assertFalse(BasicPasswordManagement::containsString("my string is this", "rash"));
	}


	/**
	 * To check if we can get the strength of a string. (3 cases are checked)
	 */
	public function testStrength()
	{
		$this->assertLessThan(0.1, BasicPasswordManagement::strength("ABCDEFGH"));
		$this->assertGreaterThan(0.5, BasicPasswordManagement::strength("Tes\$ing"));
		$this->assertLessThan(0.5, BasicPasswordManagement::strength("Tes\$ingTes\$ing"));
	}


	/**
	 * To check if we can generate a random string of given strength. (3 cases are checked)
	 */
	public function testGenerate()
	{
		$this->assertEquals(4, strlen(BasicPasswordManagement::generate(0.1)));
		$this->assertEquals(8, strlen(BasicPasswordManagement::generate(0.4)));
		$this->assertEquals(16, strlen(BasicPasswordManagement::generate(0.8)));
	}

}
