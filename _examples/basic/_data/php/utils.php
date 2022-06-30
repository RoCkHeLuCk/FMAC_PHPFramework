<?php
use FMAC\MVC\TView;

/**
 * Set alert message
 *
 * @param TView $View
 * @param string $element
 * @param string $msg
 * @param string $type
 * @return void
 */
function alertMsg(TView $View, string $element, string $msg, string $type) : void
{
   $element .= 'Alert';
   $View->$element->attribute('class')->set('alert-'.$type);
   global $translator;
   if (isset($translator->language[$msg]))
   {
      $View->$element = $translator->language[$msg];
   }   
}

/**
 * add js in html
 *
 * @param TView $View
 * @param string $loader
 * @param boolean $position
 * @return void
 */
function jsLoader(TView $View, string $loader, bool $position = true) : void
{
   $position = $position?'jsAfter':'jsBefore';
   if ($View->hasElement($position))
   {
      $js = array();
      $js[] = './public/js/js.php?'.$loader;
      $View->$position->foreachBlocks($js);
   }
}

?>
