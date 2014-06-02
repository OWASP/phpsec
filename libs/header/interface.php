<?php

namespace phpsec;

/**
 * Interface for HTTP header classes
 */
interface HeaderInterface
{
	/**
	 * Generates header object from string
	 */
	public static function fromString($headerString);

	/**
	 * Retrieves header name
	 */
	public function getKey();

	/**
	 * Retrieves header value
	 */
	public function getValue();

	/**
	 * Returns in the form "key: value"
	 * 
	 * @return string
	 */
	public function toString();
}