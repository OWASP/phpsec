<?php

namespace phpsec;

require __DIR__ . '/recaptchalib.php';

class CaptchaException extends \Exception {}

class Captcha
{
    protected static $config;

    public function __construct()
    {
        self::$config = require __DIR__ . '/config.php';
    }

    public function generateHtml()
    {
        echo recaptcha_get_html(self::$config['captcha_public_key']);
    }

    public function verifyRequest()
    {
        $response = NULL;

        if (isset($_POST['recaptcha_response_field']))
        {
            $response = recaptcha_check_answer(
                self::$config['captcha_private_key'],
                $_SERVER["REMOTE_ADDR"],
                $_POST["recaptcha_challenge_field"],
                $_POST["recaptcha_response_field"]);
            if (!$response->is_valid)
                return false;
            else
                return true;
        }
    }
}