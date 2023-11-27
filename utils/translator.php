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
               "TTranslator ERROR: Directory ({$path}) don't found"
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
            "TTranslator ERROR: File ($from) don't found"
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
               "TTranslator ERROR: Language ($language) don't found"
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

   public function findTranslater(bool $showNoUsed, string ...$directories)
   {
      if (!$this->langTran)
      {
         return;
      }

      $arrayFound = $this->langTran;
      array_walk_recursive(
         $arrayFound,
         function (&$value)
         {
            if (!is_array($value))
            {
               $value = 0;
            }
         }
      );

      $arrayNoFound = array();
      foreach ($directories as $varDir)
      {
         if (is_dir($varDir))
         {
            $fileList = listFiles(
               $varDir,
               '*.{[hH][tT][mM],[hH][tT][mM][lL],[jJ][sS]}',
               true,
               true
            );

            foreach ($fileList as $filename)
            {
               $text = file_get_contents($filename);
               $arrayMath = array();
               if (preg_match_all( '/{([\w\.]+)}/', $text, $arrayMath))
               {
                  foreach ($arrayMath[1] as $keyMath => $valueMath)
                  {
                     $levels = explode('.',$valueMath);
                     if ($levels AND array_key_exists($levels[0], $arrayFound))
                     {
                        $valueFound = &$arrayFound[$levels[0]];
                        array_shift($levels);
                        foreach ($levels as $levelKey => $levelValue)
                        {
                           if ($valueFound AND array_key_exists($levelValue, $valueFound))
                           {
                              $valueFound = &$valueFound[$levelValue];
                           } else {
                              $valueFound['#'.$levelKey] = -1;
                           }
                        }
                        if (is_numeric($valueFound))
                        {
                           $valueFound++;
                        } else {
                           $arrayNoFound[$arrayMath[1][$keyMath]][] = $filename;
                        }
                     }
                  }//foreach math
               }//if preg
            }//foreach fileList
         }//if is_dir
      }//foreach directory

      if ($arrayNoFound)
      {
         ksort($arrayNoFound, SORT_REGULAR);
         echo "<pre>";
         print_r($arrayNoFound);
         echo "</pre>";
      }

      if ($showNoUsed)
      {
         $arrayNoUsed = $arrayFound;
         $this->recursive_unset($arrayNoUsed, false);
         if ($arrayNoUsed)
         {
            ksort($arrayNoUsed, SORT_REGULAR);
            echo "<pre>";
            print_r($arrayNoUsed);
            echo "</pre>";
         }

         // $arrayUsed = $arrayFound;
         // $this->recursive_unset($arrayUsed, true);
         // echo "<pre>";
         // print_r($arrayUsed);
         // echo "</pre>";
      }
   }

   private function recursive_unset(array &$array, bool $exists)
   {
      foreach ($array as $key => &$value)
      {
         if (is_array($value))
         {
            $this->recursive_unset($value, $exists);
         } else {
            if ((($exists) AND ($value == 0))
            OR ( (!$exists) AND ($value != 0)))
            {
               unset($array[$key]);
            }
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
