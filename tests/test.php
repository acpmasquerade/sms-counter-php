<?php

error_reporting(E_ALL);
include __DIR__."/../lib/SMSCounter.php";

class SMSCounterTest extends PHPUnit_Framework_TestCase
{

	
	public function testGSM(){
	     $text = "a GSM Text";

		$count = SMSCounter::count($text);

		$expected = new stdClass();
		$expected->encoding = SMSCounter::GSM_7BIT;
		$expected->length = 10;
		$expected->per_message= 160;
		$expected->remaining = 150;
		$expected->messages = 1;

		$this->assertEquals($expected, $count);
	
	}

	public function testGSMMultiPage(){
	     $text = "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";

		$count = SMSCounter::count($text);

		$expected = new stdClass();
		$expected->encoding = SMSCounter::GSM_7BIT;
		$expected->length = 170;
		$expected->per_message= 153;
		$expected->remaining = 153 * 2 - 170;
		$expected->messages = 2;

		$this->assertEquals($expected, $count);
	
	}

	public function testUnicodeMultiPage(){
		$text = "`";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";
	     $text .= "1234567890";

		$count = SMSCounter::count($text);

		$expected = new stdClass();
		$expected->encoding = SMSCounter::UTF16;
		$expected->length = 71;
		$expected->per_message= 67;
		$expected->remaining = 67 * 2 - 71;
		$expected->messages = 2;

		$this->assertEquals($expected, $count);

	}

    public function testUnicode()
    {
        $text = "`";

		$count = SMSCounter::count($text);

		$expected = new stdClass();
		$expected->encoding = SMSCounter::UTF16;
		$expected->length = 1;
		$expected->per_message= 70;
		$expected->remaining = 69;
		$expected->messages = 1;

		$this->assertEquals($expected, $count);
    }

	public function testRemoveNonGSMChars(){
		$text = "no-unicode-remaining";

		$output = SMSCounter::remove_non_gsm_chars("`" . $text ."`");

		$this->assertEquals($text, $output);
	}

}

