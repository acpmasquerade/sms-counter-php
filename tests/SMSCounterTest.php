<?php
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

	public function testIntMapIsComplete(){
		$gsm_7bit_map = SMSCounter::int_gsm_7bit_map();
		$gsm_7bit_ex_map = SMSCounter::int_gsm_7bit_ex_map();		
		$this->assertEquals(127, count($gsm_7bit_map));
		$this->assertEquals(9, count($gsm_7bit_ex_map));
	}

	public function testEachGSM(){
		$character_set = SMSCounter::gsm_7bit_chars;
		$len = strlen($character_set);
		for($i = 0; $i< $len; $i++){
			$this->assertEquals(SMSCounter::GSM_7BIT, SMSCounter::count($character_set[$i]."")->encoding, sprintf("Testing for character %s for GSM_7BIT", $character_set[$i].""));
		}
		$extra_character_set = array("|", "^", "{", "}", "[", "]", "~", "\\", "€");
		for($i = 0; $i< count($extra_character_set); $i++){
			$char = $extra_character_set[$i];
			if(SMSCounter::utf8_to_unicode($char) == 0){
				continue;
			}
			$this->assertEquals(SMSCounter::GSM_7BIT_EX, SMSCounter::count($char."")->encoding, sprintf("Testing for character '%c' for GSM_7BIT", $char));
		}

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

	public function testCarriageReturn(){
		$text = "\n\r";
		$count = SMSCounter::count($text);

		$expected = new stdClass();
		$expected->encoding = SMSCounter::GSM_7BIT;
		$expected->length = 2;
		$expected->per_message = 160;
		$expected->remaining = 158;
		$expected->messages = 1;
		
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

    public function testSurrogateUnicode()
    {
        $text = "🎈";

		$count = SMSCounter::count($text);

		$expected = new stdClass();
		$expected->encoding = SMSCounter::UTF16;
		$expected->length = 2;
		$expected->per_message= 70;
		$expected->remaining = 68;
		$expected->messages = 1;

		$this->assertEquals($expected, $count);
    }

	public function testRemoveNonGSMChars(){
		$text = "no-unicode-remaining";

		$output = SMSCounter::remove_non_gsm_chars("`" . $text ."`");

		$this->assertEquals($text, $output);
	}

    public function testShouldForceUnicode()
    {
        $text = "foo";

        $count = SMSCounter::count($text, SMSCounter::UTF16);

        $expected = new stdClass();
        $expected->encoding = SMSCounter::UTF16;
        $expected->length = 3;
        $expected->per_message = 70;
        $expected->remaining = 67;
        $expected->messages = 1;

        $this->assertEquals($expected, $count);
    }
}

