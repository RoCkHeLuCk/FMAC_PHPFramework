<?php
namespace FMAC\MVC\Kernel;

class TKernel
{
   /**
   *    Class Name
   *    @var string
   */
   protected string $className;

   /**
   *    Class Namespace
   *    @var string
   */
   protected string $classNamespace;

   /**
   *    APP Path location
   *    @var string
   */
   protected static string $appPathLocation = '';

   /**
   *    Path location
   *    @var string
   */
   protected string $classPathLocation = '';

   /**
   *   Define Variable List MVC
   *   @var  array
   */
   private static array $mvcList = array();

   /**
   *   set global Variable in MVC
   *
   *   @method   __set
   *   @param    string           $name
   *   @param    mixed            $value
   *   @return   void
   */
   public function __set(string $name, $value) : void
   {
      TKernel::$mvcList[$name] = $value;
   }

   /**
   *   get global Variable
   *
   *   @method   __get
   *   @param    string           $name
   *   @return   mixed
   */
   public function &__get(string $name)
   {
      if (array_key_exists($name,TKernel::$mvcList))
      {
         return TKernel::$mvcList[$name];
      } else {
         if (isDebug())
         {
            echo "TKernel ERROR: Variable ou method ($name) no found";
         }
         return;
      }
   }

   /**
   *   is set global Variable in MVC
   *
   *   @method   __isset
   *   @param    string           $name
   *   @return   bool
   */
   public function __isset($name) : bool
   {
      return array_key_exists($name,TKernel::$mvcList);
   }

   /**
   *   un set global Variable in MVC
   *
   *   @method   __unset
   *   @param    string           $name
   *   @return   void
   */
   public function __unset($name) : void
   {
      if (array_key_exists($name,TKernel::$mvcList))
      {
         unset( TKernel::$mvcList[$name] );
      } else {
         if (isDebug())
         {
            echo "TKernel ERROR: Variable ou method ($name) no found";
         }
      }
   }

   /**
   *   parse current or define Namespace
   *
   *   @method   parseNS
   *   @param    int $historic
   *   @param    string $ns
   *   @return   string
   */
   protected function urlNamespace(int $historic = 0):string
   {
      static $result = '';

      if (!$historic)
      {
         if ( empty($result) )
         {
            $namespace = preg_split('/\\\/', $this->classNamespace, -1, PREG_SPLIT_NO_EMPTY);
            $result = implode('/',$namespace);
         }
         return $result;
      } else {
         $namespace = preg_split('/\\\/', $this->classNamespace, -1, PREG_SPLIT_NO_EMPTY);
         for ($i=0; $i > $historic; $i--)
         {
            array_pop($namespace);
         }
         return implode('/',$namespace);
      }
   }
}

?>