<?php
namespace Example\SubExample;
use FMAC\MVC\TController;

class Controller extends TController
{
   public function __destruct()
   {
      $this->_index->main->insertOf($this->View);
   }

   /**
    * @attribute view = subexample.html
    */
   public function execute()
   {
      if (issets($_POST,'ent1','ent2','submit'))
      {
         
      }
   }
}

?>
