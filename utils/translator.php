<?php
namespace FMAC\Utils;
use Exception;

/**
 *   TTranslator
 *   Loads the files for translation either
 *   automatically or configuring.
 */
class TTranslator
{
   /**
    *   List Language File
    *   @var   array
    */
   private array $langList = array();

   /**
    *   Index Language Selected
    *   @var  string
    */
   private string $langSelect = '';

   /**
    *   List words of translate
    *   @var  array
    */
   private array $langTran = array();

   /**
    *   Construct class
    *
    *   @method   __construct
    *   @param    string        $path   Directory Language
    */
   public function __construct(string $path = '')
   {
      if ($path)
      {
         if (!is_dir($path))
         {
            throw new Exception(
               "TTranslator ERROR: Directory ($path) dont found"
            );
         }

         $listPath = glob($path.'/*', GLOB_ONLYDIR);
         foreach ($listPath as $valuePath)
         {
            $lisFiles = glob($valuePath.'/*.php');
            foreach ($lisFiles as $valueFile)
            {
               $this->langList[basename($valuePath)][basename($valueFile,'.php')]
                  = $valueFile;
            }
         }
         $this->setLanguage('default');
      }
   }

   public function __toString() : string
   {
      return $this->langSelect;
   }

   /**
    *   Get the word translated
    *
    *   @method   __get
    *   @param    string   $key
    *   @return   array
    */
   public function __get(string $key) : array
   {
      if (array_key_exists($key,$this->langTran))
      {
         return $this->langTran[$key];
      }else{
         throw new Exception (
            "TTranslator ERROR: File ($key) dont found"
         );
      }
   }

   /**
    *   Return Region List
    *
    *   @method   getRegionList
    *   @param    string $from   //set file translate
    *   @return   array
    */
   public function getRegionList(string $from = 'language') : array
   {
      if (array_key_exists($from, $this->langTran))
      {
         $list = array();
         foreach ($this->langList as $key => $value)
         {
            $list[$key]['language'] = ifset($this->langTran[$from],$key,$key);
            if ($this->langSelect == $key)
            {
               $list[$key]['selected'] = 'true';
            } else {
               $list[$key]['selected'] = '';
            }
         }
         return $list;
      } else {
         throw new Exception (
            "TTranslator ERROR: File ($from) dont found"
         );
      }
   }

   /**
    *   set Language from translate,
    *   Blank Bears System Configuration
    *
    *   @method   setLanguage
    *   @param    string        $language
    */
   public function setLanguage(string $language = '') : void
   {
      if (empty($language))
      {
         $this->loadLanguage( $this->getSystemLang() );
      }else{
         if (array_key_exists($language, $this->langList))
         {
            $this->loadLanguage($language);
         }else{
            throw new Exception(
               "TTranslator ERROR: Language ($language) dont found"
            );
         }
      }
   }

   /**
    * Translate text
    *
    * @method string translate()
    * @param string $text
    * @param boolean $clean
    * @return string
    */
   public function translate(string $text, bool $clean = true) : string
   {
      return replaceVariables($this->langTran, $text, $clean);
   }

   /**
    *   set variable
    *
    *   @method   setVar
    *   @param    string        $name       Name the variable
    *   @param    array|scalar  $variable
    *   @param    bool          $merge
    *   @return   void
    */
   public function setVar(string $name, $variable, bool $merge = false) : void
   {
      if ($merge AND array_key_exists($name, $this->langTran) )
      {
         $this->langTran[$name] = array_merge($this->langTran[$name], $variable);
      } else {
         $this->langTran = array($name => $variable) + $this->langTran;
      }
   }

   /**
    * find all words for Translater
    *
    * @param string $directory
    * @return void
    */
   public function findTranslater(string $directory)
   {
      if ( $this->langTran AND is_dir($directory) )
      {
         $varList = $this->langTran;

         $fileList = listFiles(
            $directory,
            '*.{[pP][hH][pP],[hH][tT][mM][lL],[jJ][sS]}',
            true,
            true
         );

         $echo = '';
         $melecaList = array();
         foreach ($fileList as $filename)
         {
            $text = file_get_contents($filename);
            $findList = array();
            preg_replace_callback(
               '/{\$(\w+)(\[.*?\])?}/',
               function ($match) use (&$varList, &$findList, &$melecaList)
               {
                  if ( isset($match[1])
                  AND array_key_exists($match[1], $varList))
                  {
                     if ( isset($match[2]) )
                     {
                        if (is_array($varList[$match[1]]))
                        {
                           $listKeys = array();
                           preg_match_all(
                              '/\[[\'"]?(\w*)[\'"]?\]/',
                              $match[2],
                              $listKeys
                           );

                           $listKeys2 = array();
                           preg_match_all(
                              '/\[(?(?=[\'"])[\'"](\w*)[\'"]|(\w*))\]/',
                              $match[2],
                              $listKeys2
                           );

                           if ( !isset($listKeys[1]) OR !isset($listKeys2[1])
                           OR ( $regx = count($listKeys[1]) != count($listKeys2[1]) )
                           OR !isset_recursive_r($varList[$match[1]],$listKeys[1]))
                           {
                              $findList[] =
                                 $match[0].
                                 ($regx?' <- syntax error':' <- not found');
                           } else {
                              //isSet
                           }
                        } else {
                           $findList[] = $match[0] . ' <- not found';
                        }
                     }
                  }
               },
               $text
            );

            if ($findList)
            {
               $echo .=
                  '<h2 style="all: revert; font-size: 12px; display: block;">file: '
                  .$filename.'</h2>';
               foreach ($findList as $value)
               {
                  $echo .=
                     '<p style="all: revert; font-size: 10px; display: block;">'
                     .$value.'</p>';
               }
            }
         }

         if ($echo)
         {
            echo '<h1 style="all: revert; font-size: 14px;">Words found to translate!</h1>'.$echo.'<br/>';
         }
      }
   }



   /**
    *   Get first language of the system that has translation.
    *
    *   @method   getSystemLang
    *   @return   string
    */
   private function getSystemLang() : string
   {
      if (ifset($_SERVER,'HTTP_ACCEPT_LANGUAGE'))
      {
         preg_match_all('/\w+-?\w+/', ifset($_SERVER,'HTTP_ACCEPT_LANGUAGE'), $systemLang);
         foreach ($systemLang[0] as $value)
         {
            $value = strtolower($value);
            if (array_key_exists($value, $this->langList))
            {
               return  $value;
            }
         }
      }
      return 'default';
   }

   /**
    *   loads the list of words to be translated
    *
    *   @method   loadLanguage
    *   @param    string         $language
    */
   private function loadLanguage(string $language) : void
   {
      $this->langSelect = $language;
      if ($language == 'default')
      {
         $this->langTran = array();
      }
      foreach ($this->langList[$language] as $key => $value)
      {
         if ( array_key_exists($key,$this->langTran) )
         {
            $this->langTran[$key] = array_merge(
               $this->langTran[$key],
               require($value)
            );
         } else {
            $this->langTran[$key] = require($value);
         }
      }
   }

}
?>
