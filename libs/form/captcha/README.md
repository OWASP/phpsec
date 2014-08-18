#Usage

* Copy over `config.sample.php` to `config.php` and edit ReCaptcha API keys.

```php
<?php

require 'libs/form/captcha/captcha.php';

$captcha = new phpsec\Captcha();

if (isset($_POST['recaptcha_response_field']))
{
    if ($captcha->verifyRequest() === true)
    {
        echo 'yay';
    }
    else
    {
        echo 'nay';
    }
}
else
{
    ?>
        <form method="POST" action="/phpsec/index.php">
            <?
                $captcha->generateHtml();
            ?>
            <input type="submit">
        </form>
    <?
}

?>
```

