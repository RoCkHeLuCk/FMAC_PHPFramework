<?php

define(
   'HTML_ENTITIES_TAB',
   [
      '"'  => '&#34;',
      '$'  => '&#36;',
      '&'  => '&#38;',
      '\'' => '&#39;',
      '/'  => '&#47;',
      '<'  => '&#60;',
      '>'  => '&#62;',
      '\\' => '&#92;',
      '`'  => '&#96;',
      '{'  => '&#123;',
      '}'  => '&#125;',
   ]
);

/**
 * Convert characters to HTML Entities
 *
 * @param string  $text
 * @param boolean $trim
 *
 * @return string
 */
function strEncHTML(string $text, bool $trim = true) : string
{
   $text = strtr($text, HTML_ENTITIES_TAB);
   if ($trim)
   {
      $text = trim(preg_replace('/\s+/',' ', $text));
   }
   return $text;
}

/**
 * Convert All character in Array to HTML Entities.
 *
 * @param array   &$array
 * @param bool    $trim
 * @param string  ...$keys
 *
 * @return void
 */
function arrayEncHTML(array &$array, bool $trim = true, string ...$keys) : void
{
   if ($keys)
   {
      foreach ($keys as $key)
      {
         if (is_string($array[$key]))
            $array[$key] = strEncHTML($array[$key], $trim);
      }
   } else {
      foreach ($array as &$text)
      {
         if (is_string($text))
            $text = strEncHTML($text, $trim);
      }
   }
}

/**
*   remove all double spaces, whitespace of all text
*
*   @method   superTrim
*   @param    string      $text
*   @return   string
*/
function superTrim(string $text) : string
{
   return trim(preg_replace('/\s+/',' ', $text));
}

/**
 *   Remove Accents all text
 *   @method   removeAccents
 *   @param    string          $value
 *   @return   string
 */
function removeAccents(string $value) : string
{
    return preg_replace(
      array('/(á|à|ã|â|ä)/','/(Á|À|Ã|Â|Ä)/',
            '/(é|è|ê|ë)/','/(É|È|Ê|Ë)/',
            '/(í|ì|î|ï)/','/(Í|Ì|Î|Ï)/',
            '/(ó|ò|õ|ô|ö)/','/(Ó|Ò|Õ|Ô|Ö)/',
            '/(ú|ù|û|ü)/','/(Ú|Ù|Û|Ü)/',
            '/(ñ)/','/(Ñ)/','/(ç)/','/(Ç)/',
            '/(ý|ÿ)/','/(Ý)/'),
            explode(' ','a A e E i I o O u U n N c C y Y'),
            $value);
}

/**
 *   Gerate Random caracteres
 *   @method   strRand
 *   @param    int          $length
 *   @return   string
 */
function strRand(int $length) : string
{
   $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $chars .= 'abcdefghijklmnopqrstuvwxyz!@#$%&*:;';
   $charsLength = strlen($chars);
   $result = '';
   for ($c = 0; $c < $length; $c++)
   {
      $result .= $chars[rand(0, $charsLength - 1)];
   }
   return $result;
}

/**
 *   calculates the engineering notation,
 *   and inserts the related prefix.
 *
 *   @method   numberPrefix
 *   @param    int            $value
 *   @param    boolean        $bit      is 1024
 *   @param    array          $prefix
 *   @return   string                   number is formatted
 */
function numberPrefix(int $value, bool $bit = false,
   array $prefix = array('', 'k', 'M', 'G', 'T', 'P')) : string
{
   $bit = $bit?1024:1000;
   $pow = floor(($value?log($value):0) / log($bit));
   $pow = min($pow, count($prefix)-1);
   $value /= pow($bit, $pow);
   return  round($value,3) . ' ' . $prefix[$pow];
}


/**
*   replaces all variables within the document
*
*   @method   replaceVariables
*   @param    array              $varList
*   @param    string             $text
*   @param    bool               $clean
*   @return   string
*/
function replaceVariables(?array $varList, string $text, bool $clean = false) : string
{
   if ($varList)
   {
      return preg_replace_callback(
         '/{\$(\w+)(\[.*?\])?}/',
         function ($match) use ($varList, $clean): string
         {
            $result = $clean?'':$match[0];
            if (isset($match[1])
            AND(array_key_exists($match[1], $varList)))
            {
               $var = $varList[$match[1]];
               if (isset($match[2]))
               {
                  if (is_array($var))
                  {
                     $listKeys = array();
                     preg_match_all(
                        '/\[[\'"]?(\w*)[\'"]?\]/',
                        $match[2],
                        $listKeys
                     );
                     if (isset($listKeys[1]))
                     {
                        $result = ifset_recursive_r(
                           $var,
                           $listKeys[1],
                           $result
                        );
                     }
                  }
               } else {
                  $result = $var;
               }
            }
            if (is_null($result) OR is_scalar($result))
            {
               return strval($result);
            }else{
               return '';
            }
         },
         $text
      );
   } else {
      return '';
   }
}


/**
 * fileLoadCSV.
 *
 * @author	Franco Michel Almeida Caixeta
 * @since	v0.0.1
 * @version	v1.0.0	Tuesday, June 22nd, 2021.
 * @global
 * @param	string	$csvFile
 * @param	string	$separator
 * @return	array
 */
function fileLoadCSV(string $csvFile, string $separator) : array
{
   $result = array();
   $handle = fopen($csvFile, 'r');
   if($handle !== FALSE)
   {
      $row = array();
      while (($row = fgetcsv($handle, NULL, $separator)) !== FALSE)
      {
         $result[] = $row;
      }
      fclose($handle);
   }
   return $result;
}

?>