<?php
namespace FMAC\Utils;
use Exception;

class TInOutElement
{
   private $value = NULL;
   private bool $valid = true;
   private $callInput = NULL;
   private $callOutput = NULL;

   public function __construct(mixed $value = NULL, callable $input = NULL, callable $output = NULL)
   {
      $this->callInput = $input;
      $this->callOutput = $output;
      $this->setValue($value);
   }

   public function __toString()
   {
      return strval($this->value);
   }

   public function isValid()
   {
      return $this->valid;
   }

   public function setValue($value)
   {
      $this->value = $value;
      $this->inputCall();
   }

   public function getRaw()
   {
      return $this->value;
   }

   public function getValue()
   {
      return $this->outputCall($this->value);
   }

   private function inputCall()
   {
      if ($this->callInput)
      {
         $call = $this->callInput;
         $this->valid = $call($this->value);
      } else {
         $this->valid = true;
      }
   }

   private function outputCall()
   {
      if ($this->callOutput)
      {
         $call = $this->callOutput;
         return $call($this->value);
      } else {
         return $this->value;
      }
   }
}

class TInOut
{
   /**
    *   const bitwise options from Elements
    *
    *   @var   int
    */
   public const IN_POST      = 1;    //00000001
   public const IN_GET       = 2;    //00000010
   public const IN_COOKIE    = 4;    //00000100
   public const IN_REQUEST   = 15;   //00000111
   public const IN_SESSION   = 8;    //00001000
   public const IN_FILE      = 16;   //00010000
   public const IN_RAW       = 32;   //00100000

   public const OUT_URL      = 32;   //00100000
   public const OUT_COOKIE   = 64;   //01000000
   public const OUT_SESSION  = 128;  //10000000
}

?>