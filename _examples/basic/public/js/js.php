<?php
   require_once('../../_autoload.php');
   if (isset($_GET))
   {
      $js = '';
      foreach ($_GET as $key => $value)
      {
         $fileName = __BASEDIR__.'/_data/js/'.$key.'.js';
         if (file_exists($fileName))
         {
            $js .= file_get_contents(__BASEDIR__.'/_data/js/'.$key.'.js');
         }    
      }
      header("content-type: application/x-javascript");
      global $translator;
      echo $translator->translate( $js );
   }
?>
