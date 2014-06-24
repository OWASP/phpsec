<?php

namespace phpsec;

/**
 * Required classes
 */
require_once(__DIR__ . '/interface.php');

class HeaderException extends \Exception {}

class Header implements HeaderInterface
{
    protected $key = NULL;

    protected $value = NULL;

    public function __construct($key = NULL, $value = NULL)
    {
        if (NULL !== $key)
            $this->setKey($key);

        if (NULL !== $value)
            $this->setValue($value);
    }

    /**
     * Generates header object from string
     */
    public static function fromString($headerString)
    {
        list($key, $value) = Header::splitHeaderString($headerString);
        return new static($key, $value);
    }


    /**
     * Splits the header string in `key` and `value` parts.
     *
     * @param string $headerString
     * @return string[] `key` in the first index and `value` in the second.
     * @throws Exception\InvalidArgumentException if header does not match with the format ``key:value``
     */
    protected static function splitHeaderString($headerString)
    {
        $parts = explode(':', $headerString, 2);
        if (count($parts) !== 2)
            throw new Exception\InvalidArgumentException('Header must match with the format "key:value"');

        $parts[1] = ltrim($parts[1]);

        return $parts;
    }

    /**
     * Validates and sets header key
     */
    protected function setKey($key)
    {
        if (!is_string($key) || empty($key))
            throw new Exception\InvalidArgumentException('Header name must be a string');

        // Pre-filter to normalize valid characters, change underscore to dash
        $key = str_replace('_', '-', $key);

        /*
         * Following RFC 2616 section 4.2
         *
         * message-header   = key ":" [ value ]
         * key              = token
         *
         * @see http://tools.ietf.org/html/rfc2616#section-2.2 for token definition.
         */
        if (!preg_match('/^[!#-\'*+\-\.0-9A-Z\^-z|~]+$/', $key))
            throw new Exception\InvalidArgumentException('Header name must be a valid RFC 2616 (section 4.2) field-name.');

        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * Validates and sets header value
     */
    protected function setValue($value)
    {
        $value = (string) $value;

        if (preg_match('/^\s+$/', $value))
            $value = '';

        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * Casts key:value pair to a header string
     */
    public function toString()
    {
        return $this->getKey() . ': ' . $this->getValue();
    }

    /**
     * Checks if headers have already been sent
     *
     * @throws Exception\HeaderException if headers already sent.
     */
    public static function isSent()
    {
        if (headers_sent())
            return true;
        else
            return false;
    }

    public function set()
    {
        header($this->toString());
    }
}