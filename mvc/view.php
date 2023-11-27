<?php
namespace FMAC\MVC;
require_once(__DIR__.'/view/viewelement.php');
use FMAC\MVC\View\TViewElement;
use DOMDocument;
use DOMElement;
use Exception;

libxml_use_internal_errors(true);

/**
 *   TView
 *
 *  Manipulate DOMDocument from TView
 *
 *   @property TViewElement $id of element
 *   @property-read TViewElement $id of element
 *   @property-write TViewElement $id of element
 *   @method TViewElement __get(string $id)
 */
class TView extends TViewElement
{
   public const INSERT_BEFORE = 0;
   public const INSERT_FIRST = 1;
   public const INSERT_LAST = 2;
   public const INSERT_AFTER = 3;
   public const INSERT_REPLACE = 4;

   protected const REGEX_AMP = '/&(?!\w+;)(?!#[\dA-F]+;)/i';
   protected const REGEX_COMMENT = '/<!---.*?--->/smi';

   protected const DATA_LEVEL = 'data-level';
   protected const DATA_REMOVE = 'data-remove';
   protected const DATA_REPEAT = 'data-repeat';
   protected const DATA_RECURSIVE = 'data-recursive';
   protected const DATA_VIEW = 'data-view';
   protected const DATA_ATTRIBUTE = 'data-attribute';

   /**
    *   Pointer DOMDocument
    *
    *   @var   DOMDocument
    */
   private DOMDocument $XMLDoc;

   /**
    *   Construct TView
    *
    *   @method   __construct
    *   @param    bool       $format   XML formatted
    *   @param    string        $encoding   encode type
    */
   public function __construct(bool $format = true, string $encoding = 'UTF-8')
   {
      libxml_clear_errors();
      $this->XMLDoc = new DOMDocument('1.0', $encoding);
      $this->XMLDoc->preserveWhiteSpace = $format;
      $this->XMLDoc->formatOutput = $format;
      $this->XMLDoc->validateOnParse = false;
   }

   /**
    *   get DOMDocument
    *
    *   @method   getXMLDocument
    *   @return   DOMDocument
    */
   public function getXMLDocument() : DOMDocument
   {
      return $this->XMLDoc;
   }

   /**
    *   Load from XML or HTML Text
    *
    *   @method   loadText
    *   @param    string     $text
    */
   public function loadText(string $text, bool $XML = true) : void
   {
      $text = preg_replace(TView::REGEX_COMMENT, '', $text);
      $text = preg_replace(TView::REGEX_AMP,'&amp;',$text);

      if ($XML)
      {
         $this->XMLDoc->loadXML($text, $this->getXMLOptions());
      } else {
         $this->XMLDoc->loadHTML($text, $this->getXMLOptions());
      }

      $this->Error();
      parent::__construct(NULL, $this->XMLDoc->documentElement, '');
   }

   /**
    *   Load from XML or HTML File
    *
    *   @method   loadFile
    *   @param    string     $filename
    */
   public function loadFile(string $filename, bool $XML = true) : void
   {
      if (!file_exists($filename))
      {
         throw new Exception(
            "TView ERROR: File ($filename) no found."
         );
      }
      $this->loadText(file_get_contents($filename), $XML);
   }

   /**
    *   Save to XML or HTML text
    *
    *   @method   saveText
    *   @param    bool       $clean    clears any unmanipulated or
    *                                  empty blocks\variables\text
    *   @return   string
    */
   public function saveText(bool $clean = true, bool $XML = false) : string
   {
      if ($XML)
      {
         $XMLText = $this->XMLDoc->saveXML( NULL, $this->getXMLOptions());
      } else {
         $XMLText = $this->XMLDoc->saveHTML( NULL );
      }

      return $XMLText;
   }

   /**
    *   save to XML or HTML file
    *
    *   @method   saveFile
    *   @param    string     $fileName
    *   @param    bool       $clean    clears any un manipulated or
    *                                  empty blocks\variables\text
    */
   public function saveFile(string $fileName, bool $clean, bool $XML = false) : void
   {
      $XMLText = $this->saveText($clean, $XML);
      file_put_contents($fileName , $XMLText);
   }

   private $data = NULL;
   private array $recursive = array();
   private bool $recursiveReplace = false;

   private function foreachData_l(string $text, $array)
   {
      $result = $array;
      if ($text <> '')
      {
         $level = explode('.',$text);
         if ($level[0])
         {
            $result = $this->data;
         } else {
            unset($level[0]);
            $result = $array;
         }

         foreach ($level as $value)
         {
            if ($result AND (is_array($result)) AND array_key_exists($value, $result))
            {
               $result = $result[$value];
            } else {
               $result = '';
               break;
            }
         }
      }
      return $result;
   }

   private function foreachData_b(string $text, $array) : bool
   {
      $result = false;
      $invert = false;
      if ($text <> '')
      {
         //????????? - You have to improve security here
         if (substr($text, 0, 1) == ':')
         {
            $text = substr($text, 1);
            $expression = $this->foreachData_s($text,$array);
            $result = eval('return ('.$expression.')?true:false;');
            return $result;
         }

         if (substr($text, 0, 1) == '!')
         {
            $invert = true;
            $text = substr($text, 1);
         }
         $result = $this->foreachData_l($text, $array);
      }

      if ($result)
      {
         return $invert?false:true;
      } else {
         return $invert?true:false;
      }
   }

   private function foreachData_s(string $text, $array)
   {
      if ($text == '')
      {
         return NULL;
      }

      $count = 1;
      $loop = $this->recursiveReplace?3:1;

      while ($count AND $loop)
      {
         $text = preg_replace_callback(
            '/{([@\w\.]+)(?:\:(\w+)(?:\(([\w,]+)\))?)?}/',
            function ($match) use ($array)
            {
               $result = $this->foreachData_l($match[1],$array);

               if (isset($match[2]))
               {
                  if (function_exists($match[2]))
                  {
                     $prm = array();
                     $prm[] = $result;
                     if (isset($match[3]))
                     {
                        $prm = array_merge($prm, explode(',',$match[3]));
                     }
                     $result = call_user_func_array($match[2], $prm);
                  } else {
                     if (isDebug()) {
                        throw new Exception("TView foreachData ERROR: Function ($match[2]) no found.", 1);
                     }
                  }
               }

               if (is_null($result) OR is_scalar($result))
               {
                  return strval($result);
               }else{
                  return '';
               }
            },
            $text,
            -1,
            $count
         );
         $loop--;
      }
      return $text;
   }

   private function foreachData_a(DOMElement $XMLElement, $array, string $key = '')
   {
      $varList = (is_array($array))
         ?array_merge(['@key' => $key], $array)
         :array( '@key' => $key, '@value' => $array);

      if ($XMLElement->hasAttribute(TView::DATA_VIEW))
      {
         $level = $XMLElement->getAttribute(TView::DATA_VIEW);
         $XMLElement->removeAttribute(TView::DATA_VIEW);
         if (($view = $this->foreachData_l($level, $array))
         AND ($view instanceof TViewElement))
         {
            $XMLNew = $XMLElement->ownerDocument->importNode($view->getXMLElement(), true);
            $XMLElement->parentNode->replaceChild($XMLNew, $XMLElement);
         }
      }

      for ($i = $XMLElement->attributes->length-1; $i >= 0; $i--)
      {
         $XMLAttribute = $XMLElement->attributes[$i];
         $text = $this->foreachData_s($XMLAttribute->value, $varList);
         if ($text == '')
         {
            $XMLElement->removeAttributeNode($XMLAttribute);
         } else {
            $XMLElement->setAttribute($XMLAttribute->name, $text);
         }
      }

      for ($i = $XMLElement->childNodes->length-1; $i >= 0; $i--)
      {
         $XMLChild = $XMLElement->childNodes[$i];
         if ($XMLChild->nodeType == XML_ELEMENT_NODE)
         {
            $this->foreachData_r($XMLChild, $varList);
         } elseif (($XMLChild->nodeType == XML_TEXT_NODE) AND ($XMLChild->nodeValue)) {
            $text = $this->foreachData_s($XMLChild->nodeValue, $varList);
            $text = htmlspecialchars_decode($text);
            $XMLChild->nodeValue = $text;
         }
      }
   }

   private function foreachData_r(DOMElement $XMLElement, $array)
   {
      if ($XMLElement->hasAttribute(TView::DATA_LEVEL))
      {
         $level = $XMLElement->getAttribute(TView::DATA_LEVEL);
         $XMLElement->removeAttribute(TView::DATA_LEVEL);
         $array = $this->foreachData_l($level, $array);
      }

      if ($XMLElement->hasAttribute(TView::DATA_REMOVE))
      {
         $level = $XMLElement->getAttribute(TView::DATA_REMOVE);
         $XMLElement->removeAttribute(TView::DATA_REMOVE);
         if ($this->foreachData_b($level, $array))
         {
            $XMLElement->remove();
            return;
         }
      }

      if ($XMLElement->hasAttribute(TView::DATA_ATTRIBUTE))
      {
         $level = $XMLElement->getAttribute(TView::DATA_ATTRIBUTE);
         $XMLElement->removeAttribute(TView::DATA_ATTRIBUTE);
         if ($levelArray = $this->foreachData_l($level, $array))
         {
            foreach ($levelArray as $keyArray => $valueArray)
            {
               if (($valueArray != '') and (strval($valueArray)))
               {
                  $XMLElement->setAttribute($keyArray, $valueArray);
               }
            }
         }
      }

      if ($XMLElement->hasAttribute(TView::DATA_REPEAT))
      {
         $level = $XMLElement->getAttribute(TView::DATA_REPEAT);
         $XMLElement->removeAttribute(TView::DATA_REPEAT);
         if ( $levelArray = $this->foreachData_l($level, $array) )
         {
            foreach ($levelArray as $keyArray => $valueArray)
            {
               if (($valueArray)AND(substr($keyArray,0,1) != '@'))
               {
                  $XMLClone = $XMLElement->cloneNode(true);
                  $XMLElement->parentNode->insertBefore($XMLClone, $XMLElement);
                  $this->foreachData_a( $XMLClone, $valueArray, $keyArray);
               }
            }
         }
         $XMLElement->remove();
      } elseif ($XMLElement->hasAttribute(TView::DATA_RECURSIVE)) {
         $recursive = $XMLElement->getAttribute(TView::DATA_RECURSIVE);
         $XMLElement->removeAttribute(TView::DATA_RECURSIVE);
         if (array_key_exists($recursive, $this->recursive))
         {
            if (array_key_exists($recursive, $array) AND $array[$recursive])
            {
               $XMLClone = $this->recursive[$recursive]->cloneNode(true);
               $XMLElement->parentNode->replaceChild($XMLClone, $XMLElement);
               $this->foreachData_a($XMLClone, $array);
            } else {
               $XMLElement->remove();
            }
         } else {
            $this->recursive[$recursive] = $XMLElement;
            if (array_key_exists($recursive, $array) AND $array[$recursive])
            {
               $XMLClone = $XMLElement->cloneNode(true);
               $XMLElement->parentNode->insertBefore($XMLClone, $XMLElement);
               $this->foreachData_a($XMLClone, $array);
            }
            $XMLElement->remove();
         }
      } else {
         $this->foreachData_a($XMLElement, $array);
      }
   }

   /**
    * foreachData
    *
    * @param  array|null $varList
    *
    * @return void
    */
   public function foreachData(?array $varList, bool $recursiveReplace = false) : void
   {
      $this->recursiveReplace = $recursiveReplace;

      if ($varList)
      {
         $this->data = $varList;
         $this->foreachData_r( $this->XMLDoc->documentElement, $varList);
      }
   }

   /**
    *   show error message libxml
    *   @method   Error
    */
   private function Error() : void
   {
      $msg = '';
      foreach(libxml_get_errors() as $e)
      {
         switch ($e->code)
         {
            case 57:
            case 522:
            break;
            default:
            {
               $msg .= ' TView ERROR: '.$e->message;
            } break;
         }
      }
      if (!empty($msg))
      {
         throw new Exception($msg);
      }
      libxml_clear_errors();
   }
}
?>
