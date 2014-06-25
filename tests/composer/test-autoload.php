<?php
	include __DIR__.'/vendor/autoload.php';

	print_r(SMSCounter::count('a test string with \'`\' unicode character'));
