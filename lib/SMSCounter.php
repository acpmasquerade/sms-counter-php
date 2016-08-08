<?php

/**
 * The SMSCounter class
 * Inspired by the Javascript library https://github.com/danxexe/sms-counter
 * @author - acpmasquerade <acpamsquerade@gmail.com>
 * @date - 05th March, 2014
 * 
 * License Information
 * -------------------------------------------------------------------------------
 * | Permission is hereby granted, free of charge, to any person obtaining a copy
 * | of this software and associated documentation files (the "Software"), to deal
 * | in the Software without restriction, including without limitation the rights
 * | to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * | copies of the Software, and to permit persons to whom the Software is
 * | furnished to do so, subject to the following conditions:
 * |
 * | The above copyright notice and this permission notice shall be included in
 * | all copies or substantial portions of the Software.
 * |
 * | THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * | IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * | FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * | AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * | LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * | OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * | THE SOFTWARE.
 * -------------------------------------------------------------------------------
 */

class SMSCounter{

  # character set for GSM 7 Bit charset
  # @deprecated
  const gsm_7bit_chars = "@£\$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";
  
  # character set for GSM 7 Bit charset (each character takes two length)
  # @deprecated
  const gsm_7bitEx_chars = "\\^{}\\\\\\€[~\\]\\|";

  const GSM_7BIT = 'GSM_7BIT';
  const GSM_7BIT_EX = 'GSM_7BIT_EX';
  const UTF16 = 'UTF16';

  public static function int_gsm_7bit_map(){
    return array(10,13,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,
      51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,
      71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,
      92,95,97,98,99,100,101,102,103,104,105,106,107,108,109,110,
      111,112,113,114,115,116,117,118,119,120,121,122,
      161,163,164,165,191,196,197,198,199,201,209,214,
      216,220,223,224,228,229,230,232,233,236,241,242,
      246,248,249,252,915,916,920,923,926,928,931,934,
      936,937);
  }

  public static function int_gsm_7bit_ex_map(){
    return array(91,92,93,94,123,124,125,126,8364);
  }

  public static function int_gsm_7bit_combined_map(){
    return array_merge(self::int_gsm_7bit_map(), self::int_gsm_7bit_ex_map());
  }

  # message length for GSM 7 Bit charset
  const messageLength_GSM_7BIT = 160;
  # message length for GSM 7 Bit charset with extended characters
  const messageLength_GSM_7BIT_EX = 160;
  # message length for UTF16 charset
  const messageLength_UTF16 = 70;

  # message length for multipart message in GSM 7 Bit encoding
  const multiMessageLength_GSM_7BIT = 153;
  # message length for multipart message in GSM 7 Bit encoding with extended characters
  const multiMessageLength_GSM_7BIT_EX = 153;
  # message length for multipart message in UTF16 encoding
  const multiMessageLength_UTF16 = 67;

  /**
   * function count($text)
   * Detects the encoding, Counts the characters, message length, remaining characters
   * @return - stdClass Object with params encoding,length,per_message,remaining,messages
   */
  public static function count($text){

    $unicode_array = self::utf8_to_unicode($text);

    # variable to catch if any ex chars while encoding detection.
    $ex_chars = array();
    $encoding = self::detect_encoding($unicode_array, $ex_chars);

    if ($encoding === self::UTF16) {

      $length = 0;

      foreach($unicode_array as $uc) {

        // UTF-16 stores most characters as two bytes,
        // but it can only store 0xFFFF (= 65535) characters this way.
        // Characters above that number are stored as four bytes and
        // therefore need to count as 2 in length in a text message.
        $length += ($uc > 65535) ? 2 : 1;

      }

    } else {
      $length = count($unicode_array);

      if ( $encoding === self::GSM_7BIT_EX){
        $length_exchars = count($ex_chars);
        # Each exchar in the GSM 7 Bit encoding takes one more space
        # Hence the length increases by one char for each of those Ex chars. 
        $length += $length_exchars;
      }
    }    

    # Select the per message length according to encoding and the message length
    switch($encoding){
      case self::GSM_7BIT:
      if ( $length > self::messageLength_GSM_7BIT){
        $per_message = self::multiMessageLength_GSM_7BIT;
      }else{
        $per_message = self::messageLength_GSM_7BIT;
      }
      break;

      case self::GSM_7BIT_EX:
      if ( $length > self::messageLength_GSM_7BIT_EX){
        $per_message = self::multiMessageLength_GSM_7BIT_EX;
      }else{
        $per_message = self::messageLength_GSM_7BIT_EX;
      }
      break;

      default:
      if($length > self::messageLength_UTF16){
        $per_message = self::multiMessageLength_UTF16;
      }else{
        $per_message = self::messageLength_UTF16;
      }
      break;
    }

    $messages = ceil($length / $per_message);
    $remaining = ( $per_message * $messages ) - $length;

    $returnset = new stdClass();

    $returnset->encoding = $encoding;
    $returnset->length = $length;
    $returnset->per_message = $per_message;
    $returnset->remaining = $remaining;
    $returnset->messages = $messages;

    return $returnset;

  }

  /** 
   * function detect_encoding($text)
   * Detects the encoding of a particular text
   * @return - one of GSM_7BIT, GSM_7BIT_EX, UTF16
   */
  public static function detect_encoding ($text, & $ex_chars) {

    if(!is_array($text)){
      $text = utf8_to_unicode($text);
    }

    $utf16_chars = array_diff($text, self::int_gsm_7bit_combined_map());

    if(count($utf16_chars)){
      return self::UTF16;
    }

    $ex_chars = array_intersect($text, self::int_gsm_7bit_ex_map());

    if(count($ex_chars)){
      return self::GSM_7BIT_EX;
    }else{
      return self::GSM_7BIT;
    }

  }

  /**
   * function utf8_to_unicode ($str)
   * Generates array of unicode points for the utf8 string
   * @return array
   */
  public static function utf8_to_unicode( $str ) {

    $unicode = array();        
    $values = array();
    $looking_for = 1;

    for ($i = 0; $i < strlen( $str ); $i++ ) {

      $this_value = ord( $str[ $i ] );

      if ( $this_value < 128 ) {
       
        $unicode[] = $this_value;
      
      } else {

        if ( count( $values ) == 0 ) {
        
          if ($this_value < 224) {
            $looking_for = 2;  
          } else if ($this_value < 240) {
            $looking_for = 3;
          } else if ($this_value < 248) {
            $looking_for = 4;
          }
     
        }

        $values[] = $this_value;

        if ( count( $values ) == $looking_for ) {

          if ($looking_for == 4) {
        
            $number = ( ( $values[0] % 8 ) * 262144 ) + 
                      ( ( $values[1] % 64 ) * 4096 ) + 
                      ( ( $values[2] % 64 ) * 64 ) + 
                      ( $values[3] % 64 );
        
          } else if ($looking_for == 3) {

            $number = ( ( $values[0] % 16 ) * 4096 ) + 
                      ( ( $values[1] % 64 ) * 64 ) + 
                      ( $values[2] % 64 );
          
          } else if ($looking_for == 2) {
          
            $number = ( ( $values[0] % 32 ) * 64 ) + 
                      ( $values[1] % 64 );
          
          }

          $unicode[] = $number;
          $values = array();
          $looking_for = 1;

                } # if

            } # if
            
        } # for

        return $unicode;

    } # utf8_to_unicode

    /**
     * unicode equivalent chr() function
     * @return character
     */
    public static function utf8_chr($unicode){
      $unicode=intval($unicode);

      if($unicode<128){
        $utf8char=chr($unicode);
      }
      else if ($unicode >= 128 && $unicode < 2048){
        $utf8char = chr(192 | ($unicode >> 6)) . chr(128 | ($unicode & 0x3F));
      }
      else if ($unicode >= 2048 && $unicode < 65536){
        $utf8char = chr(224 | ($unicode >> 12)) . chr(128 | (($unicode >> 6) & 0x3F)) . chr(128 | ($unicode & 0x3F));
      }
      else{
        $utf8char = chr(240 | ($unicode >> 18)) . chr(128 | (($unicode >> 12) & 0x3F)) . chr(128 | (($unicode >> 6) & 0x3F)) . chr(128 | ($unicode & 0x3F));
      }

      return $utf8char;
    }

    /**
     * Converts unicode code points array to a utf8 str
     * @param $array - unicode codepoints array
     * @return $str - utf8 string
     */
    public static function unicode_to_utf8($array){
      $str = '';
      foreach($array as $a){
        $str .= self::utf8_chr($a);
      }

      return $str;
    }

    /**
     * Removes non GSM characters from a string
     * @return string
     */
    public static function remove_non_gsm_chars( $str ){
      # replace non gsm chars with a null character
      return self::replace_non_gsm_chars($str, null);
    }

    /**
     * Replaces non GSM characters from a string
     * @param $str - string to be replaced 
     * @param $replacement - character to be replaced with 
     * @return string
     * @return false, if replacement string is more than 1 character in length
     */
    public static function replace_non_gsm_chars( $str , $replacement = null){

      $valid_chars = self::int_gsm_7bit_combined_map();

      $all_chars = self::utf8_to_unicode($str);

      if(strlen($replacement) > 1){
        return FALSE;
      }

      $replacement_array = array();
      $unicode_arr = self::utf8_to_unicode($replacement);
      $replacement_unicode = array_pop($unicode_arr);

      foreach($all_chars as $some_position=>$some_char){
        if(!in_array($some_char, $valid_chars)){
          $replacement_array[] = $some_position;
        }
      }

      if($replacement){
        foreach($replacement_array as $some_position){
          $all_chars[$some_position] = $replacement_unicode;
        }
      }else{
        foreach($replacement_array as $some_position){
          unset($all_chars[$some_position]);
        }
      }

      return self::unicode_to_utf8($all_chars);
    }
  }
