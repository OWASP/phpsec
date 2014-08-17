#Usage

##Form

```php
require 'libs/form/csrf.php';
$csrf = new phpsec\CSRF();
```

```html
<form method="POST">
...
<? $csrf->generateHiddenField(); ?>
</form>
```

##Validation

```php
require 'libs/form/csrf.php';
$csrf = new phpsec\CSRF();
if ($csrf->verifyRequest() === true)
{
    ...
}
```
