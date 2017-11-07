<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 BoostMyshop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Helper_String extends Mage_Core_Helper_Abstract {

    /**
     *
     * @param type $html
     * @param type $start
     * @param type $end
     * @return string 
     */
    public static function getTextBetween($html, $start, $end) {
        $posStart = strpos($html, $start);
        if ($posStart > 0) {
            $posStart += strlen($start);
            $posEnd = strpos($html, $end, $posStart);
            if ($posEnd > $posStart)
                return substr($html, $posStart, ($posEnd - $posStart));
        }
        return false;
    }

    /**
     *
     * @param type $html
     * @param type $start
     * @param type $end
     * @return string 
     */
    public static function getAllTextBetween($html, $start, $end, $prefix = '') {
        $results = array();

        $posStart = strpos($html, $start);
        while ($posStart > 0) {
            $posStart += strlen($start);
            $posEnd = strpos($html, $end, $posStart);
            if ($posEnd > $posStart) {
                $value = $prefix . substr($html, $posStart, ($posEnd - $posStart));
                if (!in_array($value, $results))
                    $results[] = $value;
                $posStart = strpos($html, $start, $posEnd);
            }
            else
                $posStart = 0;
        }

        return $results;
    }

      /**
     * Extract a text beetween two tag
     *
     * @param type $textToSearchIn
     * @param type $start_tag
     * @param type $end_tag
     * @return type
     */
    public function extractTextBetweenFlags($textToSearchIn, $start_tag, $end_tag) {
      $extractedString = '';
      $startpos = strpos($textToSearchIn, $start_tag);
      //echo "startpos=$startpos<br>";
      if ($startpos !== false) {
        $startpos = $startpos + strlen($start_tag);
        $endpos = strpos($textToSearchIn, $end_tag, $startpos);
        //echo "endpos=$endpos<br>";
        if ($endpos !== false) {
          $extractedString = substr($textToSearchIn, $startpos, $endpos - $startpos);
          //echo "extractedString=$extractedString<br>";
        }
      }
      return trim($extractedString);
    }

    /**
     * Extract a text before a flag
     *
     * @param String $textToSearchIn
     * @param String $tag
     * @return String $extractedString
     */
    public function extractTextBeforeFlag($textToSearchIn, $tag) {
      $extractedString = '';
      $endpos = strpos($textToSearchIn, $tag);
      if ($endpos !== false) {
        $extractedString = substr($textToSearchIn, 0, $endpos);
      }
      return trim($extractedString);
    }

    /**
     * Extract a text after a flag
     *
     * @param String $textToSearchIn
     * @param String $tag
     * @return String $extractedString
     */
    public function extractTexAfterFlag($textToSearchIn, $tag) {
      $extractedString = '';
      $tagpos = strpos($textToSearchIn, $tag);
      if ($tagpos !== false) {
        $extractedString = substr($textToSearchIn, $tagpos + 1);
      }
      return trim($extractedString);
    }

    /**
     *
     * @param type $string
     * @return type 
     */
    public static function cleanHtml($string) {
        $string = str_replace('</table></table>', '</table>', $string);
        $string = strip_tags($string, '<p><ul><li><br>');

        $string = str_replace('é', '&eacute;', $string);
        $string = trim($string);
        return $string;
    }

    /**
     *
     * @param type $s
     * @return type 
     */
    public static function js_uni_decode($s) {
        return preg_replace('/\\\u([0-9a-f]{4})/ie', "chr(hexdec('\\1'))", $s);
    }
    
    /**
     * Normalize string  with lower case and only a -> z chars
     * @param String $subject
     */
    public function normalize($subject)
    {
        $newSubject = '';
        $subject = strtolower($subject);
        
        for ($i = 0; $i < strlen($subject); $i++) {
            if ((ord($subject[$i]) >= 97 ) and (ord($subject[$i]) <= 122 )) {
                $newSubject .= $subject[$i];
            }
        }
        
        
        return $newSubject;
    }

    /**
     * add # at begin and and of a string if no # are present
     * @param String $pattern
     */
    public function partenize($pattern){

      $pattern = strtolower(trim($pattern));

      if(strpos($pattern, '#') === false){
        $pattern = '#'.$pattern.'#';
      }

      return $pattern;
    }

    /**
     * Return the domain from an email
     * ex : $email joe@world.com
     * return world.com
     *
     */
    public function getDomainFromEmail($email){
      $domain = '';
      if(strlen($email)>3){
        $atPos = strpos($email, '@');
        if ($atPos !== false) {
          $domain = trim(substr($email, $atPos + 1));
        }
      }
      return $domain;
    }

    /**
     * Return the account from an email
     * ex : $email joe@world.com
     * return joe
     *
     */
    public function getAccountFromEmail($email){
      $account = '';
      if(strlen($email)>3){
        $atPos = strpos($email, '@');
        if ($atPos !== false) {
          $account = trim(substr($email, 0, $atPos));
        }
      }
      return $account;
    }

}