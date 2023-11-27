<?php
namespace FMAC\MVC\View;
require_once(__DIR__.'/viewattribute.php');

use DOMNode;
use DOMElement;
use DOMDocument;
use DOMNamedNodeMap;
use Exception;
use FMAC\MVC\TView;
use FMAC\MVC\View\TViewAttribute;

/**
 *   TViewElement
 *
 *   manipulate a properly formatted DOMElement
 *
 */
class TViewElement
{
   protected const NAME_ID = 'id';
   protected const DATA_ID = 'data-id';


   /**
    *  name of element, attribute NAME_ID
    */
   protected string $IDViewElement;

   /**
    *   Pointer parentViewElement
    *
    *   @var   TViewElement
    */
   private TViewElement $parentViewElement;

   /**
    *   Pointer DOMElement
    *
    *   @var   DOMElement
    */
   private DOMElement $XMLElement;

   /**
    *   TViewElement child list
    *
    *   @var  array of TViewElement
    */
   private array $ViewElementList = array();

   /**
    *   Construct TViewElement
    *
    *   @method   __construct
    *   @param    TViewElement  $parentViewElement
    *   @param    DOMElement    $XMLElement
    */
   protected function __construct(?TViewElement $parentViewElement, DOMElement $XMLElement, string $id = '')
   {
      $this->IDViewElement = $id;
      if (!is_null($parentViewElement))
      {
         $this->parentViewElement = $parentViewElement;
      }
      $this->XMLElement = $XMLElement;
   }

   /**
    *   Gets the child or self element
    *
    *   @method   __get
    *   @param    string   $id
    *   @return   TViewElement
    */
   public function __get(string $id) : TViewElement
   {
      $ViewElement = $this->getElement($id);
      if ($ViewElement)
      {
         return $ViewElement;
      }else{
         throw new Exception("TView ERROR: {$id} not found");
      }
   }

   /**
    *   Sets the child or self element
    *
    *   @method   __set
    *   @param    string   $id
    *   @param    TViewElement|TView|DOMNode|String|Numeric|NULL   $value
    *   @return   $value
    */
   public function __set(string $id, $value)
   {
      $ViewElement = $this->getElement($id);
      if (!$ViewElement)
      {
         throw new Exception("TView ERROR: {$id} not found");
      }
      $ViewElement->insertOf($value, TView::INSERT_REPLACE);
   }

   /**
    *   Convert TViewElement to String
    *
    *   @method   __toString
    *   @return   string
    */
   public function __toString() : string
   {
      return $this->getXMLDocument()->saveXML($this->XMLElement, $this->getXMLOptions());
   }

   /**
    *   Get attribute to element
    *   @method   attribute
    *   @param    string           $attribute
    *   @return   TViewAttribute
    */
   public function attribute(string $attribute) : TViewAttribute
   {
      if ($attribute != '')
      {
         return new TViewAttribute($this->XMLElement, $attribute);
      }else{
         throw new Exception('TView ERROR: Attribute name can not be empty');
      }
   }

   /**
    *   Checks if an attribute exists
    *
    *   @method   hasAttribute
    *   @param    string  $attribute
    *   @return   bool
    */
   public function hasAttribute(string $attribute) : bool
   {
      return $this->XMLElement->hasAttribute($attribute);
   }

   public function getAttributeList() : ?DOMNamedNodeMap
   {
      return $this->XMLElement->attributes;
   }

   /**
    *   Check if child exists
    *
    *   @method   hasElement
    *   @param    string       $id
    *   @return   bool
    */
   public function hasElement(string $id) : bool
   {
      return ($this->getElement($id))?true:false;
   }

   /**
    *   get getIDElement
    *
    *   @method   getIDElement
    *   @return   string
    */
   public function getIDElement() : string
   {
      return $this->IDViewElement;
   }

   /**
    *   get DOMElement
    *
    *   @method   getXMLElement
    *   @return   DOMElement
    */
   public function getXMLElement() : DOMElement
   {
      return $this->XMLElement;
   }

   /**
    *   get DOMDocument
    *
    *   @method   getXMLDocument
    *   @return   DOMDocument
    */
   public function getXMLDocument() : DOMDocument
   {
      return $this->XMLElement->ownerDocument;
   }

   /**
    *   Remove the element by List Only
    *
    *   @method   removeChild
    */
   protected function removeChild( string $id ) : void
   {
      if ($this->hasElement($id))
      {
         unset($this->ViewElementList[$id]);
      }
   }

   /**
    *   Deletes the element by id
    *
    *   @method   deleteChild
    */
   public function deleteChild( string $id ) : void
   {
      if ($this->hasElement($id))
      {
         $XMLElementChild = $this->ViewElementList[$id]->getXMLElement();
         $XMLElementChild->remove();
         unset($this->ViewElementList[$id]);
      }
   }

   /**
    *   Deletes the element itself and its children
    *
    *   @method   deleteMe
    */
   public function deleteMe() : void
   {
      $this->parentViewElement->deleteChild($this->IDViewElement);
   }

   /**
    *   Delete all your children
    *
    *   @method   cleanMe
    */
   public function cleanMe() : void
   {
      if ($this->XMLElement->childNodes)
      {
         for ($i = $this->XMLElement->childNodes->length-1; $i >= 0; $i--)
         {
            $value = $this->XMLElement->childNodes[$i];
            $value->parentNode->removeChild($value);
         }
      }
      $this->ViewElementList = array();
   }

   /**
    *   Clone the element itself and its children
    *
    *   @method   cloneMe
    *   @return   TViewElement  The clone can
    *                           only be modified
    *                           by this return
    */
   public function cloneMe(string $newID = '') : TViewElement
   {
      $XMLClone = $this->XMLElement->cloneNode(true);
      $this->cleanBlockElement($XMLClone);
      $this->XMLElement->parentNode->insertBefore($XMLClone, $this->XMLElement);
      $result = new TViewElement($this->parentViewElement, $XMLClone, $newID);
      if ($newID)
      {
         $this->XMLElement->setAttribute(TViewElement::NAME_ID, $newID);
         $this->parentViewElement->insertViewElement($result);
      }
      return $result;
   }

   /**
    *    Insert ViewElement
    *
    *    @method insertViewElement
    *    @param TViewElement $viewElement
    *    @return void
    *
    */
   protected function insertViewElement(TViewElement $viewElement)
   {
      $this->ViewElementList[$viewElement->IDViewElement] = $viewElement;
   }

   /**
    *    Insert XML File
    *
    *    @method insertXMLFile
    *    @param string $fileName
    *    @param integer $option
    *    @return void
    *
    */
   public function insertXMLFile(string $fileName, int $option = TView::INSERT_LAST) : void
   {
      $View = new TView();
      $View->loadFile($fileName);
      $this->insertOf($View, $option);
   }

   /**
    *    Insert XML Text
    *
    *    @method insertXMLText
    *    @param string $text
    *    @param integer $option
    *    @return void
    *
    */
   public function insertXMLText(string $text, int $option = TView::INSERT_LAST) : void
   {
      $View = new TView();
      $View->loadText($text);
      $this->insertOf($View,$option);
   }

   /**
    *   Inserts an element / text within the element itself
    *
    *   @method   insertOf
    *   @param    TViewAttribute|TViewElement|TView|DOMNode|String|Numeric     $valueIns
    *   @param    $option
    *
    */
   public function insertOf($valueIns, int $option = TView::INSERT_LAST) : void
   {
      if (!is_null($valueIns))
      {
         $valueIns = $this->convertToDOM($valueIns);
         if (!is_null($valueIns))
         {
            if ($valueIns->ownerDocument !== $this->getXMLDocument())
            {
               $valueIns = $this->getXMLDocument()
                  ->importNode($valueIns, true);
            }

            switch ($option)
            {
               case TView::INSERT_BEFORE:
               {
                  $this->XMLElement->insertBefore($valueIns,
                     $this->XMLElement);
               }break;

               case TView::INSERT_FIRST:
               {
                  if (!is_null($this->XMLElement->firstChild))
                  {
                     $this->XMLElement->insertBefore($valueIns,
                        $this->XMLElement->firstChild);
                  }else{
                     $this->XMLElement->appendChild($valueIns);
                  }
               }break;

               case TView::INSERT_LAST:
               {
                  $this->XMLElement->appendChild($valueIns);
               }break;

               case TView::INSERT_AFTER:
               {
                  if (!is_null($this->XMLElement->nextSibling))
                  {
                     $this->XMLElement->insertBefore($valueIns,
                        $this->XMLElement->nextSibling);
                  }else{
                     $this->XMLElement->parentNode->appendChild($valueIns);
                  }
               }break;

               case TView::INSERT_REPLACE:
               {
                  $this->XMLElement->parentNode->replaceChild($valueIns, $this->XMLElement);
               }break;
            }
         }
      }
   }

   /**
    *   value in clone a block and replace
    *   the text of the element / attribute / child
    *
    *   @method   oneBlock
    *   @param    mixed            $value
    *
    */
   public function oneBlock($value) : ?TViewElement
   {
      $XMLOldText = $this->__toString();
      $XMLNewDoc = new DOMDocument();

      $varList = array('value' => $value);
      $XMLNewText = replaceVariables($varList, $XMLOldText);
      $XMLNewText = preg_replace(TView::REGEX_AMP,'&amp;',$XMLNewText);
      $XMLNewDoc->loadXML($XMLNewText, $this->getXMLOptions());
      $this->cleanBlockElement($XMLNewDoc->documentElement);
      $XMLNewElement = $this->getXMLDocument()->importNode(
         $XMLNewDoc->documentElement, true);

      if (!is_null($XMLNewElement))
      {
         $this->XMLElement->parentNode->
            insertBefore($XMLNewElement,$this->XMLElement);
         return new TViewElement($this->parentViewElement,$XMLNewElement);
      }
      return NULL;
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
            $XMLNewText = replaceVariables($varList, $XMLOldText);
            $XMLNewText = preg_replace(TView::REGEX_AMP,'&amp;',$XMLNewText);
            $XMLNewDoc->loadXML($XMLNewText, $this->getXMLOptions());
            $XMLNewElement = $this->XMLDoc->importNode(
               $XMLNewDoc->documentElement, true);

            if (!is_null($XMLNewElement))
            {
               $XMLOldDoc->parentNode->replaceChild($XMLNewElement, $XMLOldDoc);
            }
         }
      }
   }

   /**
    *   For all values in an array clone a block and replace
    *   the text of the element / attribute / child
    *
    *   @method   foreachBlocks
    *   @param    array           $array
    *             multi-dimensional array
    */
   public function foreachBlocks(array $array) : void
   {
      $XMLOldText = $this->__toString();
      $XMLNewDoc = new DOMDocument();

      foreach ($array as $key => $arrayValues)
      {
         $varList = array(
            'key' => $key,
            'value' => $arrayValues
         );
         $XMLNewText = replaceVariables($varList,$XMLOldText);
         $XMLNewText = preg_replace(TView::REGEX_AMP,'&amp;',$XMLNewText);
         $XMLNewDoc->loadXML($XMLNewText, $this->getXMLOptions());
         $this->cleanBlockElement($XMLNewDoc->documentElement);
         $XMLNewElement = $this->getXMLDocument()->importNode(
            $XMLNewDoc->documentElement, true);

         if (!is_null($XMLNewElement))
         {
            $this->XMLElement->parentNode->
               insertBefore($XMLNewElement,$this->XMLElement);
         }
      }
   }

   /**
    *   replace text in the element, its children and attributes
    *
    *   @method   replaceStr
    *   @param    mixed       $search
    *   @param    mixed       $replace
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

      $XMLOldText = $this->__toString();
      $XMLNewText = str_replace($search, $replace, $XMLOldText);
      $XMLNewText = preg_replace(TView::REGEX_AMP,'&amp;',$XMLNewText);

      $XMLNewDoc = new DOMDocument();
      $XMLNewDoc->loadXML($XMLNewText, $this->getXMLOptions());
      $XMLNewElement = $this->getXMLDocument()->importNode(
         $XMLNewDoc->documentElement, true);

      if (!is_null($XMLNewElement))
      {
         $this->XMLElement->parentNode->replaceChild($XMLNewElement, $this->XMLElement);
         $this->XMLElement = $XMLNewElement;
      }
   }

   /**
    *   clean optinos BlockElement
    *   @method   cleanBlockElement
    *   @param    DOMElement          $XMLNewElement
    */
   private function cleanBlockElement(DOMElement $XMLNewElement) : void
   {
      $XMLNewElement->removeAttribute(TViewElement::NAME_ID);
      if ($XMLNewElement AND $XMLNewElement->hasAttribute(TViewElement::DATA_ID) )
      {
         $newID = $XMLNewElement->getAttribute(TViewElement::DATA_ID);
         $XMLNewElement->setAttribute(TViewElement::NAME_ID, $newID);
         $XMLNewElement->removeAttribute(TViewElement::DATA_ID);
      }
   }

   /**
    *   Convert Types to DOMNode
    *
    *   @method   convertToDOM
    *   @param    TViewAttribute|TViewElement|
    *             TView|DOMNode|String|Numeric    $value
    *   @return   DOMNode|NULL
    */
   private function convertToDOM($value) : ?DOMNode
   {
      if (is_null($value) OR is_scalar($value))
      {
         return $this->getXMLDocument()->createTextNode(''.$value);
      }

      if ($value instanceof TView)
      {
         return $value->getXMLDocument()->documentElement;
      }

      if ($value instanceof TViewElement)
      {
         return $value->getXMLElement();
      }

      if ($value instanceof DOMNode)
      {
         return $value;
      }

      throw new Exception( 'TView ERROR: this type is not supported' );
   }

   /**
    *   get Element by TViewElement::NAME_ID
    *
    *   @method   getElement
    *   @param    string       $id
    *   @return   TViewElement|NULL
    */
   private function getElement(string $id) : ?TViewElement
   {
      if (array_key_exists($id,$this->ViewElementList))
      {
         return $this->ViewElementList[$id];
      }else{
         $XMLElementList = $this->XMLElement->getElementsByTagName('*');
         foreach ($XMLElementList as $XMLElement)
         {
            if($XMLElement->hasAttribute(TViewElement::NAME_ID))
            {
               $name = $XMLElement->getAttribute(TViewElement::NAME_ID);
               if ($name == $id)
               {
                  $this->ViewElementList[$name] = new TViewElement($this, $XMLElement, $name);
                  return $this->ViewElementList[$name];
               }
            }
         }
         return NULL;
      }
   }

   /**
    *   Create configurate type
    *
    *   @method   getXMLOptions
    *   @return   int
    */
   protected function getXMLOptions() : int
   {
      return
         LIBXML_BIGLINES | //Permite que números de linha maiores que 65535 sejam informados corretamente.
         //LIBXML_PARSEHUGE | //Define a bandeira xml_parse_huge, que relaxa qualquer limite hardcode do analisador. Isso afeta os limites como a profundidade máxima de um documento ou a repouso da entidade, bem como limites do tamanho dos nós de texto.
         //LIBXML_COMPACT | //compacta nodes
         //LIBXML_DTDATTR | //Padrão de atributos DTD
         //LIBXML_DTDLOAD | //Carrega o subset externo
         //LIBXML_DTDVALID | //Valida com o DTD
         //LIBXML_NOBLANKS| //exclue nodes em branco
         //LIBXML_NOCDATA | //Fundi CDATA com text nodes
         LIBXML_NOEMPTYTAG | //expande nodes <br/> para <br></br>
         //LIBXML_NOENT | //Substitue entidades
         //LIBXML_NOERROR| //suprime erros
         //LIBXML_NONET | //Desabilita o acesso a rede quando carregando documentos
         //LIBXML_NOWARNING | //suprime avisos
         //LIBXML_NOXMLDECL | //exclue declarações cabeçalho
         //LIBXML_NSCLEAN | //remove declarações redundantes namespace
         //LIBXML_XINCLUDE | //Implementa substituições XInclude
         //LIBXML_ERR_ERROR | //recupera o erros
         //LIBXML_ERR_FATAL | //erro fatal
         //LIBXML_ERR_NONE | //sem erros
         //LIBXML_ERR_WARNING | //simples aviso
         //LIBXML_VERSION | //Versão da libxml como 20605 ou 20617
         //LIBXML_DOTTED_VERSION | //Versão da libxml como 2.6.5 ou 2.6.17
         //LIBXML_SCHEMA_CREATE | //Criar nós de valor padrão / fixo durante a validação do esquema XSD
         LIBXML_HTML_NOIMPLIED | //Define o sinalizador HTML_PARSE_NOIMPLIED, que desativa a adição automática de elementos implícitos de html / body ....
         //LIBXML_HTML_NODEFDTD | //Define o sinalizador HTML_PARSE_NODEFDTD, que impede que um tipo de documento padrão seja adicionado quando um não é encontrado.
         0;
   }
}
?>
