<?php
namespace FMAC\MVC;
require_once(__DIR__.'/view/viewelement.php');
use FMAC\MVC\View\TViewElement;
use DOMDocument;
use DOMXpath;
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
   public function __construct(bool $format = true,
      string $encoding = 'UTF-8')
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
    *   Load from XML Text
    *
    *   @method   loadText
    *   @param    string     $text
    */
   public function loadText(string $text) : void
   {
      $text = preg_replace('/<!---.*?--->/smi', '', $text);
      $text = preg_replace(TViewElement::REPLACE_AMP,'&amp;',$text);
      $this->XMLDoc->loadXML($text, $this->getXMLOptions());
      $this->Error();
      parent::__construct(NULL, $this->XMLDoc->documentElement);
   }

   /**
    *   Load from XML File
    *
    *   @method   loadFile
    *   @param    string     $filename
    */
   public function loadFile(string $filename) : void
   {
      if (!file_exists($filename))
      {
         throw new Exception(
            "TView ERROR: File ($filename) no found."
         );
      }
      $this->loadText(file_get_contents($filename));
   }

   /**
    *   Save to XML text
    *
    *   @method   saveText
    *   @param    bool       $clean    clears any unmanipulated or
    *                                  empty blocks\variables\text
    *   @return   string
    */
   public function saveText(bool $clean = true, bool $XML = false) : string
   {
      if ($clean)
      {
         $this->cleanElementsBlocks();
      }
      if ($XML)
      {
         $XMLText = $this->XMLDoc->saveXML( NULL, $this->getXMLOptions());
      } else {
         $XMLText = $this->XMLDoc->saveHTML( NULL );
      }

      if ($clean AND (TViewElement::NAME_ID != 'id'))
      {
         $XMLText = preg_replace('/ '.TViewElement::NAME_ID.
            '=[\"\']\w*?[\"\']/', '', $XMLText);
      }
      return $XMLText;
   }

   /**
    *   save to XML file
    *
    *   @method   saveFile
    *   @param    string     $fileName
    *   @param    bool       $clean    clears any unmanipulated or
    *                                  empty blocks\variables\text
    */
   public function saveFile(string $fileName, bool $clean, bool $XML = false) : void
   {
      $XMLText = $this->saveText($clean, $XML);
      file_put_contents($fileName , $XMLText, FILE_TEXT);
   }

   /**
    *    Foreach Elements "ID"
    *
    *    @method  foreachElements
    *    @param   array $array     Key correspond ID Value correspond $value
    *    @return  void
    */
   public function foreachElements(array $array) : void
   {
      $XMLNewDoc = new DOMDocument();
      $XMLNewDoc->preserveWhiteSpace = false;

      foreach ($array as $key => $value)
      {
         if ($this->hasElement($key))
         {
            $XMLOldDoc = $this->$key->getXMLElement();
            $XMLOldText = $this->$key->__toString();
            $varList = array('value' => $value);
            $XMLNewText = replaceVariables($varList,$XMLOldText);
            $XMLNewText = preg_replace(TViewElement::REPLACE_AMP,'&amp;',$XMLNewText);
            $XMLNewDoc->loadXML($XMLNewText, $this->getXMLOptions());
            $XMLNewElement = $this->XMLDoc->importNode(
               $XMLNewDoc->documentElement, true);

            if (!is_null($XMLNewElement))
            {
               $XMLOldDoc->parentNode->replaceChild($XMLNewElement, $XMLOldDoc);
               $this->__refresh();
            }
         }
      }
   }

   /**
    *   clean empty Blocks
    *
    *   @method   cleanElementsBlocks
    */
   private function cleanElementsBlocks() : void
   {
      $XMLXPath = new DOMXpath($this->XMLDoc);
      $this->XMLDoc->normalizeDocument();

      $XMLElementList = $XMLXPath->query('//*[@'.TViewElement::NAME_BLOCK.']');
      foreach ($XMLElementList as $XMLElement)
      {
         $XMLElement->parentNode->removeChild($XMLElement);
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
