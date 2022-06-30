<?php
namespace FMAC\MVC\View;
use DOMElement;
use DOMAttr;

/**
 *   TViewAttribute
 *
 *   Manipulates the attributes of an TViewElement
 */
class TViewAttribute
{
   /**
    *   Pointer DOMAttr to DOMElement
    *
    *   @var DOMElement
    */
   private DOMElement $XMLownerElement;

   /**
    *   Name to attribute
    *
    *   @var   string
    */
   private string $AttName = '';

   /**
    *   Contruct TViewAttribute
    *
    *   @method   __construct
    *   @param    DOMElement       $XMLownerElement
    *   @param    string           $AttName
    */
   public function __construct(DOMElement $XMLownerElement,
      string $AttName)
   {
      $this->XMLownerElement = $XMLownerElement;
      $this->AttName = $AttName;

      if (!$this->XMLownerElement->hasAttribute($AttName))
      {
         $this->XMLownerElement->setAttribute($AttName,'');
      }
   }

   /**
    *   Convert to String
    *
    *   @method   __toString
    *   @return   string
    */
   public function __toString() : string
   {
      return $this->get();
   }

   /**
    *   Get DOMAttr
    *
    *   @method   getDOMAttr
    *   @return   DOMAttr
    */
   public function getDOMAttr() : DOMAttr
   {
      return $this->XMLownerElement->getAttributeNode($this->AttName);
   }

   /**
    *   Set text to attribute
    *
    *   @method   set
    *   @param    string|NULL   $value
    */
   public function set(?string $value) : void
   {
      $this->XMLownerElement->setAttribute($this->AttName,
         superTrim(''.$value));
   }

   /**
    *   get attribute values
    *
    *   @method   get
    *   @return   string
    */
   public function get() : string
   {
      return $this->XMLownerElement->getAttribute($this->AttName);
   }

   /**
    *   Add text to attribute
    *
    *   @method   add
    *   @param    string   $value
    */
   public function add(string $value) : void
   {
      $novo = $this->get().' '.$value;
      $this->set($novo);
   }

   /**
    *   Detele text from attribute
    *
    *   @method   del
    *   @param    string   $value
    */
   public function del(string $value) : void
   {
      $novo = str_replace($value,'',$this->get());
      $this->set($novo);
   }

   /**
    *   Checks whether a text exists
    *
    *   @method   has
    *   @param    string    $value
    *   @return   bool
    */
   public function has(string $value) : bool
   {
      return (stripos($this->get(),$value) === true);
   }

   /**
    *   Alternates between the existence text in attribute
    *
    *   @method   toggle
    *   @param    string   $value
    */
   public function toggle(string $value) : void
   {
      if (stripos($this->get(),$value) === false)
      {
         $this->add($value);
      }else{
         $this->del($value);
      }
   }

   /**
    * Replace Strings in Attibute content.
    *
    * @author	Franco M. A Caixeta
    * @since	v0.0.1
    * @version	v1.0.0	2021/06/07.
    * @access	public
    * @param	mixed	$search
    * @param	mixed	$replace
    * @return	void
    */
   public function replaceStr($search, $replace) : void
   {
      if (is_array($search))
      {
         foreach ($search as &$value)
         {
            $value = '{'.$value.'}';
         }
      }else{
         $search = '{'.$search.'}';
      }

      $AttOldText = $this->get();
      $AttNewText = str_replace($search, $replace, $AttOldText);
      $this->set($AttNewText);
   }

   /**
    *   Deletes the attribute itself
    *
    *   @method   deleteMe
    */
   public function deleteMe() : void
   {
      $this->XMLownerElement->removeAttribute($this->AttName);
   }
}

?>
