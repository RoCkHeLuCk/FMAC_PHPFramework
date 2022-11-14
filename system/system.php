<?php

/**
 *   tests if multiple elements of the same level exist
 *   in an array
 *
 *   @method   issets_r
 *   @param    array   $array
 *   @param    array   $keys
 *   @return   bool
 */
function issets_r(?array $array, array $keys) : bool
{
   if ((!$array) OR (!$keys))
   {
      return false;
   }

   foreach ($keys as $key)
   {
      if (!array_key_exists($key,$array))
      {
         return false;
      }
   }

   return true;
}

/**
 *   tests if multiple elements of the same level exist
 *   in an array
 *
 *   @method   issets
 *   @param    array   $array
 *   @param    string   $keys
 *   @return   bool
 */
function issets(?array $array, string ...$keys) : bool
{
   return issets_r($array, $keys);
}

/**
 *   test if an element in element, recursive an array exists
 *
 *   @method   isset_recursive
 *   @param    array            $array
 *   @param    array            $keys
 *   @return   bool
 */
function isset_recursive_r(?array $array, array $keys) : bool
{
   if (!($array) OR !($keys))
   {
      return false;
   }

   $key = array_shift($keys);

   if (is_array($key))
   {
      if (!issets_r($array, $key))
      {
         return false;
      }
   } else {
      if (!array_key_exists($key,$array))
      {
         return false;
      }
   }

   if (!$keys)
   {
      return true;
   }

   if (!is_array($array[$key]))
   {
      return false;
   }

   return isset_recursive_r($array[$key] , $keys);
}

/**
 *   test if an element in element, recursive an array exists
 *
 *   @method   isset_recursive
 *   @param    array            $array
 *   @param    string | array   $keys
 *   @return   bool
 */
function isset_recursive(?array $array, ...$keys) : bool
{
   return isset_recursive_r($array, $keys);
}


/**
 *   test if an element in an array exists and
 *   returns its value if it does not return $unset
 *
 *   @method   ifset
 *   @param    array   $array
 *   @param    string   $key
 *   @param    mixed   $unexist
 *   @return   mixed
 */
function ifset(?array $array, string $key, $unset = NULL)
{
   if (!$array)
   {
      return $unset;
   }
   return (array_key_exists($key,$array)?$array[$key]:$unset);
}

/**
 *   test if an element in multi array,
 *   exists and returns its value in first array,
 *   if it does not return $unset
 *
 *   @method   ifset_multi
 *   @param    mixed            $unset
 *   @param    array            $keys
 *   @param    array            ...$arrays
 *   @return   mixed
 */
function ifset_multi($unset, string $key, ?array ...$arrays)
{
   if ($arrays)
   {
      foreach ($arrays as $arrayValue)
      {
         if (($arrayValue) AND (array_key_exists($key,$arrayValue)))
         {
            return $arrayValue[$key];
         }
      }
   }
   return $unset;
}

/**
 *   find first array is not empty and return it,
 *   if it does not return array empty
 *
 *   @method   ifset_array
 *   @param    array        ...$arrays
 *   @return   array
 */
function ifset_array(?array ...$arrays) : array
{
   if ($arrays)
   {
      foreach ($arrays as $arrayValue)
      {
         if ($arrayValue)
         {
            return $arrayValue;
         }
      }
   }
   return array();
}

/**
 *   test if an element in element, recursive an array
 *   exists and returns its value if it does not return $unset
 *
 *   @method   ifset_recursive_r
 *   @param    array            $array
 *   @param    array            $keys
 *   @param    mixed            $unset
 *   @return   mixed
 */
function ifset_recursive_r(?array $array, array $keys, $unset = NULL)
{
   if ((!$array) OR (!$keys))
   {
      return $unset;
   }

   $key = array_shift($keys);
   if (!array_key_exists($key,$array))
   {
      return $unset;
   }

   if (!$keys)
   {
      return $array[$key];
   }

   if (!is_array($array[$key]))
   {
      return $unset;
   }

   return ifset_recursive_r($array[$key],$keys,$unset);
}

/**
 *   test if an element in element, recursive an array
 *   exists and returns its value if it does not return $unset
 *
 *   @method   ifset_recursive
 *   @param    mixed            $unset
 *   @param    array            $array
 *   @param    string           $keys...
 *   @return   mixed
 */
function ifset_recursive($unset, ?array $array, string ...$keys)
{
   return ifset_recursive_r($array, $keys, $unset);
}

/**
 *   test if is numeric return value if is not return $unnull
 *   @method   ifnumeric
 *   @param    mixed      $num
 *   @param    mixed      $unnull
 *   @return   mixed
 */
function ifnumeric($num, $unnull = 0)
{
   return (is_numeric($num)?$num:$unnull);
}

/**
 *   List all files and sub directory files.
 *
 *   @method   listFiles
 *   @param    string      $path
 *   @param    string      $mask
 *   @param    boolean     $subPath
 *   @param    boolean     $incAll
 *   @return   array
 */
function listFiles(string $path, string $mask, bool $subPath = true,
   bool $incAll = false) : array
{
   $result = array();
   foreach (glob($path.'/'.$mask, GLOB_BRACE ) as $filePath)
   {
      $name = substr(basename($filePath),0,1);
      if ( ($incAll OR (($name <> '_') AND ($name <> '.')))
      AND (!is_dir ($filePath)))
      {
         $result[] = $filePath;
      }
   }

   if ($subPath)
   foreach (glob($path.'/*', GLOB_ONLYDIR) as $directory)
   {
      $name = substr(basename($directory),0,1);
      if ($incAll OR (($name <> '_') AND ($name <> '.')))
      {
         $result = array_merge($result,listFiles($directory, $mask, $subPath, $incAll));
      }
   }

   return $result;
}

/**
 *   get Attributes tag class or method.
 *
 *   @method   getAttribute
 *   @param    string      $class
 *   @param    string      $method
 *   @return   array
 */
function getAttribute(string $class, string $method = '') : array
{
   if (class_exists($class))
   {
      $reflection = new ReflectionClass($class);
      if (method_exists($class,$method))
      {
         $reflection = $reflection->getMethod($method);
      }
   } else {
      if (function_exists($method))
      {
         $reflection = new ReflectionMethod($method);
      }
   }

   $pregResult = array();
   preg_match_all(
      '/@ATTRIBUTE +([A-Z_]\w*) *= *(.+);/i',
      $reflection->getDocComment(),
      $pregResult
   );

   $result = array();
   if($pregResult)
   foreach ($pregResult[1] as $key => $value)
   {
      $result[$value] = $pregResult[2][$key];
   }

   return $result;
}


/**
 * autoLoadPHP require_once all files
 *
 * @param string $path
 * @return void
 */
function autoLoadPHP(string $path) : void
{
   $listPhpFiles = listFiles($path,'/*.php');
   foreach ($listPhpFiles as $value)
   {
      require_once($value);
   }
}
?>
