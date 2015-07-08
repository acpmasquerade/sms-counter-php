#SMS Counter (PHP)
Character counter for SMS Messages
Original insipration : [danxexe/sms-counter](https://github.com/danxexe/sms-counter)

[![Build Status](https://travis-ci.org/acpmasquerade/sms-counter-php.svg?branch=master)](https://travis-ci.org/acpmasquerade/sms-counter-php)

##Usage
```php
SMSCounter::count('some-string-to-be-counted');	
```
which returns
```
stdClass Object
(
	[encoding] => GSM_7BIT
	[length] => 25
	[per_message] => 160
	[remaining] => 135
	[messages] => 1
)
```
##Installation
`sms-counter-php` is available via [composer](http://getcomposer.org) on [packagist](https://packagist.org/packages/acpmasquerade/sms-counter-php).  
```json
{
    "require": {
        "acpmasquerade/sms-counter-php": "dev-master"
    }
}
```

##License
SMS Counter (PHP) is released under the [MIT License](LICENSE.txt)
