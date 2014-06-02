<?php

namespace phpsec;

/**
 * Interface for HTTP header classes
 */
interface HeaderInterface
{
	public static function fromString($headerString);

	public function getKey();

	public function getValue();

	public function toString();
}