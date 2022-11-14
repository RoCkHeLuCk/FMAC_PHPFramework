<?php
require_once('system.php');

/**
 *   set DEBUG
 *
 *   @var   boolean
 */
$GLOBALS['DEBUG'] = false;

/**
*   Set enable DEBUG
*
*   @method   setDebug
*   @param    bool       $enabled
*/
function setDebug(bool $enabled) : void
{
   $GLOBALS['DEBUG'] = $enabled;
   if ( $enabled )
   {
      if (function_exists('opcache_reset'))
      {
         opcache_reset();
      }
      error_reporting(E_ALL);
      ini_set('display_errors', true);
      ini_set('html_errors', true);
      //ini_set('xdebug.collect_params', '3');
      // ini_set('xdebug.force_display_errors','1');
      // ini_set('xdebug.show_error_trace','1');
      // ini_set('xdebug.show_exception_trace','1');
      // ini_set('xdebug.collect_vars', '1');
      // ini_set('xdebug.collect_params', '4');
      // ini_set('xdebug.show_local_vars','1');
      ini_set('xdebug.var_display_max_depth', '10');
      // ini_set('xdebug.var_display_max_children', '256');
      // ini_set('xdebug.var_display_max_data', '256');
      // ini_set('xdebug.cli_color','1');
   }else{
      error_reporting(0);
      ini_set("display_errors", false);
      ini_set("html_errors", false);
      // ini_set('xdebug.force_display_errors','0');
      // ini_set('xdebug.show_error_trace','0');
      // ini_set('xdebug.show_exception_trace','0');
      // ini_set('xdebug.collect_vars', '0');
      // ini_set('xdebug.collect_params', '0');
      // ini_set('xdebug.show_local_vars','0');
      // ini_set('xdebug.var_display_max_depth', '10');
      // ini_set('xdebug.var_display_max_children', '256');
      // ini_set('xdebug.var_display_max_data', '256');
      // ini_set('xdebug.cli_color','0');
      // ini_set('xdebug.remote_autostart','0');
      // ini_set('xdebug.remote_enable','0');
   }
}

/**
*   Checks if DEBUG is defined and if it is true
*
*   @method   isDebug
*   @return   bool
*/
function isDebug() : bool
{
   return $GLOBALS['DEBUG'];
}


/**
 *   Record the microtime of the beginning of the test
 *
 *   @var   float
 */
$GLOBALS['startTimeTest'] = 0;

/**
 *   Record the microtime of the beginning and Tick of the test
 *
 *   @var   float
 */
$GLOBALS['tickTimeTest'] = 0;


/**
 *   start Time Test
 *
 *   @method   startTimeTest
 */
function startTimeTest() : void
{
   $GLOBALS['startTimeTest'] = microtime(true);
   $GLOBALS['tickTimeTest'] = microtime(true);
}

/**
 * Tick Time Test return interval
 *
 * @author	Franco Michel Almeida Caixeta
 * @since	v0.0.1
 * @version	v1.0.0	Wednesday, June 9th, 2021.
 * @global
 * @param	bool	$reset	(tick time reset)
 * @return	mixed
 */
function tickTimeTest(bool $reset) : string
{
   $time_end = microtime(true) - $GLOBALS['tickTimeTest'];
   $time_end *= 1000000;
   if ($reset)
   {
      $GLOBALS['tickTimeTest'] = microtime(true);
   }
   return numberPrefix($time_end, false, array('u','m','')).'s';
}


/**
 *   stop Time Test return interval
 *
 *   @method   stopTimeTest
 *   @return   string   numeric formated prefix
 */
function stopTimeTest() : string
{
   $time_end = microtime(true) - $GLOBALS['startTimeTest'];
   $time_end *= 1000000;
   return numberPrefix($time_end, false, array('u','m','')).'s';
}

/**
 *   return report server Performance Test
 *
 *   @method   serverPerforTest
 *   @return   array           [time], [memory], [memory_true]
 */
function serverPerforTest() : array
{
   $time_end = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
   $time_end *= 1000000;
   $result = array();
   $result['time'] = numberPrefix($time_end,false,array('u','m','')).'s';
   $result['memory'] =  numberPrefix(memory_get_usage(false), true).'B';
   $result['memory_true'] =  numberPrefix(memory_get_usage(true), true).'B';
   return $result;
}

/**
 *   send message to browser console
 *
 *   @method   consoleLog
 *   @param    mixed       $message
 */
function consoleLog($message) : void
{
   $message = serialize($message);
   echo "<script>
            console.log( '$message' );
         </script>";
}
?>
