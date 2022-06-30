<?php
namespace Example;
use FMAC\MVC\TController;

class Controller extends TController
{
   /**
    * @attribute view = example.html
    */
   public function execute()
   {
      $this->_index->main->insertOf($this->View);
   }
}

?>
