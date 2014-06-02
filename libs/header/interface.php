<?php

namespace phpsec;

/**
 * Interface for HTTP header classes
 */

interface HeaderInterface
{
	public static function fromString($header);

	public function getFieldName();

	public function getFieldValue();

	public function toString();
}