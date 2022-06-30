<?php
use FMAC\MVC\TController;

class Controller extends TController
{
   /**
   * @attribute view = index.html;
   */
   public function __construct()
   {
      global $translator;
      $this->_translator = $translator;
      $this->_index = $this->View;
      $css = array();
      $css[] = './public/css/helper.css';
      $this->_index->css->foreachBlocks($css);
   }

   public function __destruct()
   {
      if ($error = $this->getErrorCode())
      {
         $fileName = __BASEDIR__.'/_data/html/error'.
            $error.'.html';

         if (file_exists($fileName))
         {
            $this->View->main->insertXMLFile($fileName);
         } else {
            $this->View->main->insertOf(
               'ERROR Code: '.$error);
         }
      }
      echo $this->_translator->translate( $this->View->saveText() );
   }

   public function execute()
   {
      TController::loadController(__APPDIR__,'/login');
   }
}

?>
