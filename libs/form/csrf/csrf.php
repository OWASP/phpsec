<?php

namespace phpsec;

require __DIR__ . '/../../core/random.php';

class CSRFException extends \Exception {}

class CSRF
{
    protected static $namespace = '_csrf';

    public function __construct($namespace = '_csrf')
    {
        self::$namespace = $namespace;

        if (session_id() === '')
        {
            session_start();
        }

        $this->setToken();
    }

    /**
     * Verify if supplied token matches the stored token
     *
     * @param string $token
     * @return boolean
     */
    public function isValidToken($token)
    {
        return hash_equals($token, $this->getToken());
    }

    /**
     * Generates the HTML input field with the token
     */
    public function generateHiddenField()
    {
        $token = $this->getToken();
        echo '<input type="hidden" name="' . self::$namespace . '" value="' . $token . '" />';
    }

    /**
     * Verifies whether the post token was set, else dies with error
     */
    public function verifyRequest()
    {
        $check = !isset($_POST[self::$namespace]) || !$this->isValidToken($_POST[self::$namespace]);

        $this->refreshToken();

        if ($check)
            throw new CSRFException("CSRF Validation Failed");
        else
            return true;
    }

    /**
     * Generates a new token value and saves it in session
     */
    private function setToken()
    {
        $token = $this->getToken();

        if ($token === false)
        {
            $token = Rand::randStr(32);
            $this->writeTokenToSession($token);
        }
    }

    /**
     * Reads token from session
     * @return string
     */
    public function getToken()
    {
        if (isset($_SESSION[self::$namespace]))
        {
            return $_SESSION[self::$namespace];
        }
        else
        {
            return false;
        }
    }

    /**
     * Writes token to session
     */
    private function writeTokenToSession($token)
    {
        $_SESSION[self::$namespace] = $token;
    }

    /**
     * Refresh token stored in session
     */
    private function refreshToken()
    {
        unset($_SESSION[self::$namespace]);

        $this->setToken();
    }
}
