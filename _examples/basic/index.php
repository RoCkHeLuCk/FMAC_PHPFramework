<?php
   require_once(__DIR__.'/_autoload.php');
   
   use FMAC\MVC\TController;
   TController::autoload(
      __APPDIR__,
      ifset($_GET,'pg',''),
      ifset($_GET,'ac','')
   );
?>
