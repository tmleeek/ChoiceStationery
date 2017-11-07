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
 * @copyright  Copyright (c) 2012 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Guillaume SARRAZIN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_CrmTicket_Model_Email_MailParser_Part extends MDN_CrmTicket_Model_Email_MailParser_Abstract {



 /**
  * Parse a Zend_Mail_Part
  *
  * http://framework.zend.com/apidoc/1.0/Zend_Mail/Zend_Mail_Part.html
  * and fill a MDN_CrmTicket_Model_EmailStructured
  *
  */
  public function parse(&$emailStructured, $part, &$debug) {

    $content = null;

    $contentToDecodeAfterCharset = false;
    $charsetDecoded = false;
    $transfertEncodingDecoded = false;
    $contentTypeDecoded = false;

    $responseContentType = '';
    $attachementFilename = '';
    $attachementType = null;

    if ($part != null) {

      $stringHelper = Mage::helper('CrmTicket/String');

      $content = $part->getContent();

      if ($content != null) {

        //Manage Transfert Encoding
        try {
          $transfertEncoding = $part->getHeader('Content-Transfer-Encoding');

          if ($transfertEncoding) {

            $debug[] = "transfertEncoding=$transfertEncoding<br/>";

            switch ($transfertEncoding) {
              case 'quoted-printable':
                $content = $this->decodeQuotedPrintable($content);
                $transfertEncodingDecoded = true;
                $debug[] = "quoted_printable_decode passed<br/>";
                break;
              case 'base64':
                $content = base64_decode($content);
                $transfertEncodingDecoded = true;
                $debug[] = "base64_decode passed<br/>";
                break;
              case '7bit': //ascii
              case '8bit': //ascii extended
                $contentToDecodeAfterCharset = true;
                break;

              case 'binary': // for file, nothing to do
              default:
                break;
            }
          } else {
            $debug[] = "No transfertEncoding detected<br>";
          }
        } catch (Zend_Mail_Exception $zmex) {
          //ignore these exceptions
        } catch (Exception $ex) {
          $debug[] = "Transfert Encoding exception:$ex<br>";
        }

        //SPEC http://www.w3.org/Protocols/rfc1341/7_3_Message.html
        //Content Type
        $contentTypeCompleteHeader = '';

        try {
          $contentTypeCompleteHeader = $part->getHeader('Content-Type');
        } catch (Zend_Mail_Exception $zmex) {
          //ignore these exceptions
        } catch (Exception $ex) {
          $debug[] = "Content type exception:$ex<br>";
        }

        if ($contentTypeCompleteHeader) {
          $debug[] = "contentTypefromheaders=$contentTypeCompleteHeader<br>";

          //better algo should be to use "explode" ..., but not so simple
          $contentTypeSecondPart = trim($stringHelper->extractTexAfterFlag($contentTypeCompleteHeader, ';'));
          $flagName = strtolower(trim($stringHelper->extractTextBeforeFlag($contentTypeSecondPart, '=')));
          $flagValue = trim($stringHelper->extractTexAfterFlag($contentTypeSecondPart, '='));

          //Charset and format Management
          $charset = $flagValue;
          $format = '';

          if (strtolower($flagName) == 'charset') {

            if (strpos($flagValue, ";") > 0) {
              $charset = $stringHelper->extractTextBeforeFlag($flagValue, ';');
              $formatString = $stringHelper->extractTexAfterFlag($flagValue, ';');
              if (strtolower($stringHelper->extractTextBeforeFlag($formatString, '=')) == 'format') {
                $format = $stringHelper->extractTexAfterFlag($flagValue, '=');
                $format = str_replace('"', "", strtolower($format));
              }
            }
            $charset = str_replace('"', "", strtolower($charset));

            $debug[] = "charset=$charset<br/>";
            $debug[] = "format=$format<br/>";

            //http://stackoverflow.com/questions/374425/convert-utf8-characters-to-iso-88591-and-back-in-php
            //Charset List : http://www.iana.org/assignments/character-sets
            //PHP supports :
            //ISO-8859-1	ISO8859-1	 Europe occidentale, Latin-1.
            //ISO-8859-5	ISO8859-5	 Jeu de caractère cyrillique rarement utilisé (Latin/Cyrillic).
            //ISO-8859-15	ISO8859-15	 Europe occidentale, Latin-9. Dispose du signe Euro, des caractères spéciaux français et finlandais, qui manquent au Latin-1 (ISO-8859-1).
            //UTF-8	 	 Unicode 8 bits multioctets, compatible avec l'ASCII
            //cp866	ibm866, 866	 Jeu de caractères Cyrillique spécifique à DOS.
            //cp1251	Windows-1251, win-1251, 1251	 Jeu de caractères Cyrillic spécifique à Windows.
            //cp1252	Windows-1252, 1252	 Jeu de caractères spécifique de Windows pour l'Europe occidentale.
            //KOI8-R	koi8-ru, koi8r	 Russe.
            //BIG5	950	 Chinois traditionnel, principalement utilisé à Taïwan.
            //GB2312	936	 Chinois simplifié, officiel.
            //BIG5-HKSCS	 	 Big5 avec les extensions de Hong Kong, chinois traditionnel.
            //Shift_JIS	SJIS, SJIS-win, cp932, 932	 Japonais
            //EUC-JP	EUCJP, eucJP-win	 Japonais
            //MacRoman	 	 Jeu de caractères utilisé par Mac OS.

            if ($charset) {

              //trick to mange all latin case -> Not a good idea (greek, russian ...)
//              if(strpos($charset,'iso-8859-')>0 || strpos($charset,'latin-')>0 || strpos($charset,'windows-')>0){
//                $charset = 'latin';
//              }
              //trick to manage all UTF-8 case -> OK
              $originalCharset = $charset;
              if (strpos($charset, 'utf-') > 0 || strpos($charset, 'ucs-') > 0) {
                $charset = 'utf';
              }


              $isLatin = false;
              $charsetDB = 'ISO-8859-15';
              if ($this->_isDatabaseFormatIsUTF8) {
                $charsetDB = 'UTF-8';
              }

              switch ($charset) {
                case 'utf':
                  //$debug[] = "Real Encoding Method 1 =" . mb_detect_encoding($content) . '<br/>';
                  //echo "Real Encoding Method 2 =";
                  //var_dump(iconv_get_encoding($content));
                  //echo '<br/>';
                  if (!$this->_isDatabaseFormatIsUTF8) {
                    try {
                      $content = iconv(strtoupper($originalCharset), $charsetDB, $content); //iconv convert to ISO-8859-15 to keep € etc ...
                      $debug[] = "utf iconv $originalCharset to $charsetDB  passed<br>";
                      $charsetDecoded = true;
                    } catch (Exception $ex) {
                      $content = utf8_decode($content); //utf8_decode convert to ISO-8859-1
                      $debug[] = "utf uft-8_decode passed<br>";
                      $charsetDecoded = true;
                    }
                  }
                  break;
                case 'windows-1250'://latin 2
                case 'windows-1252'://latin 1 Microsoft extended
                case 'CP1252': //latin9
                case 'iso-8859-15': //latin 9 
                case 'iso-8859-1': //latin 1
                case 'iso-8859-2': //latin 2
                  $isLatin = true;
                  if ($this->_isDatabaseFormatIsUTF8) {
                    try {
                      $content = iconv(strtoupper($originalCharset), $charsetDB, $content); //iconv convert to ISO-8859-15 to keep € etc ...
                      $debug[] = "utf iconv $originalCharset to $charsetDB  passed<br>";
                      $charsetDecoded = true;
                    } catch (Exception $ex) {
                      $content = utf8_encode($content); //utf8_decode convert to ISO-8859-1
                      $debug[] = "utf utf8_encode passed<br>";
                      $charsetDecoded = true;
                    }
                  }
                  break;

                //try to manage all other languages
                //ex : case 'GB2312': //Chinese
                default:
                  if (!$isLatin && !$charsetDecoded) {
                    try {
                      $content = iconv(strtoupper($originalCharset), $charsetDB, $content);
                      $debug[] = "default iconv $originalCharset to $charsetDB  passed<br>";
                    } catch (Exception $ex) {
                      $debug[] = "default iconv $originalCharset to $charsetDB failed<br>";
                    }
                  }
                  break;
              }
            }

            if ($format) {
              switch ($format) {

                case 'flowed': //todo
                  //Manage the "format=flowed" case : Content-Type: text/plain; charset="iso-8859-1"; format="flowed"
                  //http://joeclark.org/ffaq.html
                  break;
                default:
                  break;
              }
            }
          }

          //htmlentities have to pass AFTER a uft8decode
          if (!$this->_isDatabaseFormatIsUTF8) {
            if ($contentToDecodeAfterCharset) {
              $content = htmlentities($content);
              $content = $this->keepMoneyCharacters($content);
            }
          }

          //sometime we detect filemane in content type
          //FileName Management
          if ($flagName == 'name') {
            $attachementFilename = str_replace('"', "", $flagValue);
            $debug[] = "filename detected in content type=$attachementFilename <br>";
          }
          


          //Manage Content Type
          //$content_type = substr($contentType, 0, strpos($contentType, ';'));
          $content_type = trim($stringHelper->extractTextBeforeFlag($contentTypeCompleteHeader, ';'));
          if(empty($content_type) && !empty($contentTypeCompleteHeader)){
            $content_type = $contentTypeCompleteHeader;
          }



          if ($content_type) {
            $debug[] = "content_type detected=" . $content_type . '<br>';

            $content_type_categ = trim($stringHelper->extractTextBeforeFlag($content_type, '/'));

            //Todo define autorized content type
            if ($content_type_categ) {
              $debug[] = "content_type_categ detected=" . $content_type_categ . '<br>';

             // ref http://webdesign.about.com/od/multimedia/a/mime-types-by-content-type.htm
             switch ($content_type_categ) {
                case 'message':
                  break;
                case 'multipart':
                  break;
                case 'text':
                  break;
                case 'video':
                   //$attachementType = 'video';//Autorize all this attachements // VIDEO are Disabled
                   break;
                case 'audio':
                  //$attachementType = 'audio';//Autorize all this attachements // AUDIO are Disabled
                  break;
                case 'image':
                  $attachementType = 'image';//Autorize all image attachements
                  break;
                case 'x-world':
                   break;
                case 'application':
                  $attachementType = 'application';//other software like otocad ...
                  default:
                break;
             }
            }


            //http://filext.com/faq/office_mime_types.php
            
            switch ($content_type) {
//              case 'multipart/mixed':
//              case 'multipart/alternative':
//                $params = array();
//                $params['raw'] = $part->getContent();
//                $part = new Zend_Mail_Part($params);
//                self::parsePart($part);
//                return;
//                break;
              case 'text/plain':
                //$content = trim($content);
                //$content = trim(urldecode($content));
                $content = trim($this->formatPreformattedText($content));
                $responseContentType = 'text/plain';
                $contentTypeDecoded = true;
                $debug[] = "trim+formatPreformattedText passed<br>";
                break;
              case 'text/html':
                $content = trim($this->cleanNonAcceptableHtmlElements($content));
                //$content = trim(htmlspecialchars_decode(html_entity_decode($content)));//cause more pb than solve
                $responseContentType = 'text/html';
                $contentTypeDecoded = true;
                $debug[] = "trim+htmlspecialchars_decode+html_entity_decode passed<br>";
                break;
              case 'message/rfc822': // mailer deamon body of the previous mail
              case 'message/delivery-status': //mail acknowledgment of receipt
                $content = ''; //ignore content
                $contentTypeDecoded = true;
                break;
              case 'image/gif':
              case 'image/jpeg':
              case 'image/png':
              case 'image/tiff':
                $attachementType = 'image';
                break;
              case 'application/xml':
              case 'text/xml':
                $attachementType = 'xml';
                break;
              case 'application/pdf':
              case 'X-unknown/pdf':
                $attachementType = 'pdf';
                break;
              case 'application/msword':
              case 'application/rtf':
              case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                $attachementType = 'doc';
                break;
              case 'application/vnd.ms-excel':
              case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                $attachementType = 'xls';
                break;
              case 'application/vnd.ms-powerpoint':
              case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
              case 'application/vnd.openxmlformats-officedocument.presentationml.slideshow':
                $attachementType = 'ppt';
                break;
              case 'application/x-gzip':
              case 'application/zip':
              case 'application/x-zip-compressed':
              case 'application/x-tar':
              case 'application/x-gtar':
              case 'multipart/x-zip':
              case 'multipart/x-gzip':
                $attachementType = 'zip';
                break;
              //case 'application/octet-stream':
              default:
                break;
            }

            if ($attachementType)
              $debug[] = "attachementType detected=" . $attachementType . '<br>';
            }
        } else {
          $debug[] = "No Content Type detected<br>";
          $content = trim($this->formatPreformattedText($content));
          $debug[] = "trim+formatPreformattedText passed<br>";
        }

        

        //content-disposition
        if (!$attachementFilename) {
          try {
            $contentDisposition = $part->getHeader('content-disposition');
            if ($contentDisposition) {
              $debug[] = "contentDisposition=$contentDisposition<br>";

              //manage Content Type
              if ($contentDisposition) {
                $attachement = strtok($contentDisposition, ';');
                if ($attachement == 'attachement' || $attachement == 'attachment' || $attachement == 'inline') {
                  $filename_item = strtok(';');                  
                  if (strpos($filename_item, 'filename') >= 0) {
                    //$attachementFilename = strtok($filename_item, '=');//old version
                    $attachementFilename = $stringHelper->extractTexAfterFlag($filename_item, '=');
                    $attachementFilename = $this->attachementNameFormatter($attachementFilename);
 
                    $debug[] = "Filename detected in content disposition=$attachementFilename";
                  }
                }
              }
            }
          } catch (Zend_Mail_Exception $zmex) {
            //ignore these exceptions
          } catch (Exception $ex) {
            $debug[] = "Transfert Encoding exception:$ex<br>";
          }
        }

        ///Exclude forbidden attachements type
        if($attachementFilename){
            $forbiddenAttachementFlags = array('.php', '.js');//hardcoded security for now
            foreach ($forbiddenAttachementFlags as $flag) {
              if(strpos(strtolower($attachementFilename), $flag)>0)
                   $attachementFilename= '';
            }
        }


        //content is an attachement or a response

        if ($attachementFilename && $attachementType) {
          if (Mage::getStoreConfig('crmticket/attachements/parse_attachement_on_email_import')) {
            $emailStructured->attachements[$attachementFilename] = $content;
            $emailStructured->attachementsType[$attachementFilename] = $attachementType;
            $debug[] = "<br/><b>RESULT : attachement Extracted ($attachementType) : $attachementFilename </b><br/>";
          }
        } else {
          $debug[] = "<br/><b>RESULT : response Extracted ($responseContentType) : <br/><p>$content</p></b><br/>";

          if ($content) {
            //Sometime (mail multipart/alternative), some mail contains the same response in many format
            //here : we save the reponse with a preference for 'text/html'
            if (!$emailStructured->response) {
              $emailStructured->response = $content;
              $emailStructured->responseContentType = $responseContentType;
              $debug[] = "<br/><b>Response Saved !  for $responseContentType</b><br/>";
            } else {
              if ($emailStructured->responseContentType == 'text/plain' && $responseContentType == 'text/html') {
                $emailStructured->response = $content;
                $emailStructured->responseContentType = $responseContentType;
                $debug[] = "<br/><b>Response Replaced & Saved !  for $responseContentType</b><br/>";
              }
            }
          } else {
            $debug[] = "<br/><b>RESULT response content was empty !  ($responseContentType) : <br/><p>$content</p></b><br/>";
          }
        }
      } else {
        $debug[] = "<br><br>part was empty !!<br>";
      }
    }
  }


   /**
   * It seems that quoted_printable_decode ignore/remove =0D=0A (\r\n)
   * So we use here a custom fonction that prepare the string depending of carriage return element we find in this String
   *
   * @param String $data
   */
  protected function decodeQuotedPrintable($data) {

    $prepared = false; //to avoid multiple carriage return inserred
    $cr = '<br/>';
    if (strpos($data, '=0D') > 0) {
      $data = str_replace('=0D', $cr, $data);
      $prepared = true;
    }
    if (!$prepared && strpos($data, '=0A') > 0) {
      $data = str_replace('=0A', $cr, $data);
    }
    //preg_replace('/[^\r\n]{73}[^=\r\n]{2}/', "$0=\r\n", $content); //patch a essayer, vu sur la doc php
    return quoted_printable_decode($data);
  }

  /**
   * Forced to do this because htmlentities kill this caracters in text plain case
   * @param type $data
   */
  protected function keepMoneyCharacters($data) {
    //dollar is OK because $ is in Latin1
    //these symbaols are in latin 9, so they are loose by most of PHP function like htmlentities
    //echo "<br/>Euro code =".ord('€')." symbol pos=".strpos($data, chr(ord('€')))."<br/>";
    $data = str_replace('€', '&euro;', $data); //€
    $data = str_replace('¢', '&cents;', $data); //¢
    $data = str_replace('£', '&pound;', $data); //£
    $data = str_replace('¥', '&yen;', $data); //¥
    $data = str_replace('¤', '&curren;', $data); //¤
    //to complete
    return $data;
  }

 

  /**
   * Clean head, css and js elements from a mail
   *
   * @param type $html
   * @return type
   */
  protected function cleanNonAcceptableHtmlElements($html) {

    //$lenBefore=strlen($html);
    $search = array("'<script[^>]*?>.*?</script>'si", //remove js
        "'<style[^>]*?>.*?</style>'si", //remove css
        "'<head[^>]*?>.*?</head>'si", //remove head
        "'<link[^>]*?>.*?</link>'si", //remove link
        "'<object[^>]*?>.*?</object>'si");
    $replace = array("", "", "", "", "");
    $formattedHtml = preg_replace($search, $replace, $html);
    //$lenAfter = strlen($formattedHtml);
    //echo "lenBefore=$lenBefore lenAfter=$lenAfter <br/>";
    return $formattedHtml;
  }

   /**
   * It seems that quoted_printable_decode ignore/remove =0D=0A (\r\n)
   * So we use here a custom fonction that prepare the string depending of carriage return element we find in this String
   *
   * @param String $data
   */
  protected function formatPreformattedText($data) {
    $prepared = false;
    $cr = '<br>';

    //echo '<br>20 first elements :<pre>';
    //var_dump(unpack('C*', substr($data,0,40))); //to display each byte of the string
    //echo '</pre><br>';

    /*
    $eol = $this->detectEol($data);
    if ($eol != '') {
      $data = str_replace($eol, $cr, $data);
      $prepared = true;
    }
    */
    //todo boucler sur un while !prepared
    if (strpos($data, '<br/>') > 0) {
      $data = str_replace('<br/>', $cr, $data);
    }
    if (!$prepared && strpos($data, chr(10)) > 0) {
      $data = str_replace(chr(10), $cr, $data);
      $prepared = true;
    }
    if (!$prepared && strpos($data, chr(13)) > 0) {
      $data = str_replace(chr(13), $cr, $data);
      $prepared = true;
    }
    if (!$prepared && strpos($data, chr(30)) > 0) {
      $data = str_replace(chr(30), $cr, $data);
      $prepared = true;
    }
    if (!$prepared && strpos($data, chr(15)) > 0) {
      $data = str_replace(chr(15), $cr, $data);
      $prepared = true;
    }
    return $data;
  }

   protected function detectEol($str) {
    static $eols = array(
"\0x000D000A", // [UNICODE] CR+LF: CR (U+000D) followed by LF (U+000A)
"\0x000A", // [UNICODE] LF: Line Feed, U+000A
"\0x000B", // [UNICODE] VT: Vertical Tab, U+000B
"\0x000C", // [UNICODE] FF: Form Feed, U+000C
"\0x000D", // [UNICODE] CR: Carriage Return, U+000D
"\0x0085", // [UNICODE] NEL: Next Line, U+0085
"\0x2028", // [UNICODE] LS: Line Separator, U+2028
"\0x2029", // [UNICODE] PS: Paragraph Separator, U+2029
"\0x0D0A", // [ASCII] CR+LF: Windows, TOPS-10, RT-11, CP/M, MP/M, DOS, Atari TOS, OS/2, Symbian OS, Palm OS
"\0x0A0D", // [ASCII] LF+CR: BBC Acorn, RISC OS spooled text output.
"\0x0A", // [ASCII] LF: Multics, Unix, Unix-like, BeOS, Amiga, RISC OS
"\0x0D", // [ASCII] CR: Commodore 8-bit, BBC Acorn, TRS-80, Apple II, Mac OS <=v9, OS-9
"\0x1E", // [ASCII] RS: QNX (pre-POSIX)
"\0x15", // [EBCDEIC] NEL: OS/390, OS/400
    );
    $cur_cnt = 0;
    $cur_eol = '';
    foreach ($eols as $eol) {
      if (($count = substr_count($str, $eol)) > $cur_cnt) {
        $cur_cnt = $count;
        $cur_eol = $eol;
      }
    }
    return $cur_eol;
  }

}
