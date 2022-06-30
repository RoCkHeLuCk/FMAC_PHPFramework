<?php
namespace FMAC\Utils;
use Exception;

/**
 *   Element Request manipulation
 */
class TRequestElement
{
   /**
    *   Cookie 31/12/2038
    *
    *   @var   int
    */
   private const COOKIE_2038 = 2147483647;

   /**
    *   name
    *
    *   @var   string
    */
   private string $name = '';

   /**
    *   array key is cookie
    *
    *   @var   string
    */
   private string $key = '';

   /**
    *   value var in out
    *
    *   @var   mixed
    */
   private $value = '';

   /**
    *   is Validate value
    *
    *   @var   bool
    */
   private bool $isValid = true;


   /**
    *   bitwise options IO
    *
    *   @var   int
    */
   private int $ioBitwise = 0;


   /**
    *   Define ERROR Message
    *   @var   string
    */
   private string $errorMessage = '';

   /**
    *   function call validate input
    *
    *   @var   callable
    */
   private $callValidate = NULL;

   /**
    *   construc Request Element
    *
    *   @method   __construct
    *   @param    string        $name
    *   @param    string        $key
    *   @param    int           $ioBitwise
    *   @param    callable      $validate
    */
   public function __construct(string $name, string $key = '',
      int $ioBitwise = 0, callable $callValidate = NULL)
   {
      $this->name = $name;
      $this->key = $key;
      $this->ioBitwise = $ioBitwise;
      $this->callValidate = $callValidate;

      if ((TRequest::IN_POST & $ioBitwise) AND (isset($_POST[$name])))
      {
         $this->value = strval($_POST[$name]);
      }else{
         if ((TRequest::IN_GET & $ioBitwise) AND (isset($_GET[$name])))
         {
            $this->value = strval($_GET[$name]);
         }else{
            if ((TRequest::IN_COOKIE & $ioBitwise) AND (isset($_COOKIE[$name])))
            {
               $this->value = strval($_COOKIE[$name]);
            }else{
               if ((TRequest::IN_SESSION & $ioBitwise) AND (isset($_SESSION[$name])))
               {
                  $this->value = strval($_SESSION[$name]);
               }
            }
         }
      }
      $this->validate();
      $this->save();
   }

   /**
    *   Convert request to String
    *
    *   @method   __toString
    *   @return   string
    */
   public function __toString() : string
   {
      return $this->value;
   }

   /**
    *   get value Element
    *
    *   @method   getValue
    *   @return   string
    */
   public function getValue() : string
   {
      return $this->value;
   }

   /**
    *   Set value Element
    *
    *   @method   setValue
    *   @param    string     $value
    */
   public function setValue(string $value) : void
   {
      $this->value = $value;
      $this->save();
   }

   /**
    *   validate value in
    *
    *   @method   validate
    *   @return   bool
    */
   private function validate() : bool
   {
      if ($this->callValidate)
      {
         $call = $this->callValidate;
         return $call($this);
      }
      return true;
   }

   /**
    *   is out URL
    *
    *   @method   isURL
    *   @return   bool
    */
   public function isURL() : bool
   {
      return (TRequest::OUT_URL & $this->ioBitwise) AND ($this->isValid);
   }

   /**
    *   save cookie and session
    *
    *   @method   save
    */
   private function save() : void
   {
      if ((TRequest::OUT_COOKIE & $this->ioBitwise) AND ($this->isValid))
      {
         setcookie($this->key.'['.$this->name.']',
            $this->value,
            self::COOKIE_2038,
            './; samesite=strict',
            '',
            true,
            true
         );
      }

      if ((TRequest::OUT_SESSION & $this->ioBitwise) AND ($this->isValid))
      {
         $_SESSION[$this->key][$this->name] = $this->value;
      }
   }

   /**
    *   set error message
    *
    *   @method   setErrorMessage
    *   @param    string    $errorMessage
    */
   public function setErrorMessage(string $errorMessage) : void
   {
      $this->errorMessage = $errorMessage;
   }

   /**
    *   get error message
    *
    *   @method   getErrorMessage
    *   @return   string
    */
   public function getErrorMessage() : string
   {
      return $this->errorMessage;
   }

}

/**
 *   Request class manipulation Request/save cookie
 */
class TRequest
{
   /**
    *   const bitwise options from Elements
    *
    *   @var   int
    */
   public const IN_POST      = 1;    //00000001
   public const IN_GET       = 2;    //00000010
   public const IN_COOKIE    = 4;    //00000100
   public const IN_SESSION   = 8;    //00001000
   public const IN_REQUEST   = 15;   //00001111
   public const OUT_URL      = 16;   //00010000
   public const OUT_COOKIE   = 32;   //00100000
   public const OUT_SESSION  = 64;   //01000000

   /**
    *   Request Element List
    *
    *   @var   array
    */
   private array $requestList = array();

   /**
    *   Cookie Key array separator
    *
    *   @var   string
    */
   private string $key = '';

   /**
    *   construct Request Controller
    *
    *   @method   __construct
    *   @param    string        $key
    */
   public function __construct(string $key = '')
   {
      $this->key = $key;
   }

   /**
    *   add Element Request controller
    *
    *   @method   add
    *   @param    string   $name
    *   @param    int      $ioBitwise
    *   @param    [type]   $validate
    */
   public function add(string $name, int $ioBitwise,
      ?callable $CallValidate = NULL) : void
   {
      $this->requestList[$name] = new TRequestElement($name,
         $this->key, $ioBitwise, $CallValidate);
   }

   /**
    *   get Element value
    *
    *   @method   __get
    *   @param    string   $key
    *   @return   string
    */
   public function __get(string $key) : string
   {
      if (array_key_exists($key, $this->requestList))
      {
         return $this->requestList[$key];
      }else{
         throw new Exception(
            "TRequest ERROR: Element ($key) no found"
         );
      }
   }

   /**
    *   set Element value
    *
    *   @method   __set
    *   @param    string   $key
    *   @param    string   $value
    */
   public function __set(string $key, string $value)
   {
      if (array_key_exists($key, $this->requestList))
      {
         $this->requestList[$key]->setValue($value);
      }else{
         throw new Exception(
            "TRequest ERROR: Element ($key) no found"
         );
      }
      return $value;
   }

   public function getArray(string ...$keys) : array
   {
      $result = array();
      foreach ($this->requestList as $reqKey => $reqValue)
      {
         if ((!$keys) or (array_key_exists($reqKey, $keys)))
         {
            $result[$reqKey] = $reqValue->getValue();
         }
      }
      return $result;
   }

   /**
    *   parsed Url encoding
    *
    *   @method   parseURL
    *   @return   string
    */
   public function parseURL() : string
   {
      $result = array();
      foreach ($this->requestList as $reqKey => $reqValue)
      {
         if ($reqValue->isURL())
         {
            $result[$reqKey] = $reqValue->getValue();
         }
      }
      return './?'.http_build_query($result);
   }

   /**
    *   Header url
    *   @method   Header
    */
   public function Header() : void
   {
      header('Location: '.$this->parseURL());
   }
}
?>
