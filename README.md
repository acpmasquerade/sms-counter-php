#SMS Counter (PHP)
Character counter for SMS Messages
Original insipration : [danxexe/sms-counter](https://github.com/danxexe/sms-counter)

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

##License
SMS Counter (PHP) is released under the [MIT License](LICENSE.txt)
