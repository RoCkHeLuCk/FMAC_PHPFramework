<?php
namespace FMAC\MVC;
require_once(__DIR__.'/kernel/kernel.php');
require_once(__DIR__.'/view.php');
use FMAC\MVC\Kernel\TKernel;
use ReflectionClass;
use ReflectionProperty;
use ReflectionMethod;

/**
*   TController
*
*   Class Controller
*/
abstract class TController extends TKernel
{
   /**
   *   Define Variable List Controller
   *   @var  array
   */
   private array $ctrlList = array();

   /**
   *   Define PRIVILEGES Bitwise
   *   @var   int
   */
   private static int $havePrivilege = 0;

   /**
   *   Define ERROR Code
   *   @var   int
   */
   private static int $errorCode = 0;

   /**
   *    function execute class
   *    @method mixed execute()
   */
   abstract public function execute();

   /**
   *   set global Variable in TController
   *
   *   @method   __set
   *   @param    string           $name
   *   @param    mixed            $value
   *   @return   void
   */
   public function __set(string $name, $value) : void
   {
      if ($name[0] == '_')
      {
         parent::__set($name, $value);
      } else {
         $this->ctrlList[$name] = $value;
      }
   }

   /**
   *   get global Variable in TController
   *
   *   @method   __get
   *   @param    string           $name
   *   @return   mixed
   */
   public function &__get(string $name)
   {
      if ($name[0] == '_')
      {
         return parent::__get($name);
      } else {
         switch ($name)
         {
            case 'Model':
            {
               if (!isset($this->ctrlList['Model']))
               {
                  $modelClass = $this->classNamespace.'\Model';
                  if (class_exists($modelClass))
                  {
                     $this->ctrlList['Model'] = new $modelClass();
                  } else {
                     $this->ctrlList['Model'] = new TModel();
                  }
               }
               return $this->ctrlList['Model'];
            }break;

            case 'View':
            {
               if (!isset($this->ctrlList['View']))
               {
                  $this->ctrlList['View'] = new TView();
               }
               return $this->ctrlList['View'];
            }break;

            case 'toConstruct':
            {
               if (!isset($this->ctrlList['toConstruct']))
               {
                  $this->ctrlList['toConstruct'] = true;
               }
               return $this->ctrlList['toConstruct'];
            }

            case 'toDestruct':
            {
               if (!isset($this->ctrlList['toDestruct']))
               {
                  $this->ctrlList['toDestruct'] = true;
               }
               return $this->ctrlList['toDestruct'];
            }

            default:
            {
               if (array_key_exists($name, $this->ctrlList))
               {
                  return $this->ctrlList[$name];
               } else {
                  if (isDebug())
                  {
                     echo "TController ERROR: Variable ou method ($name) no found";
                  }
                  return NULL;
               }
            }break;
         }
      }
   }

   /**
   *   is set global Variable in TController
   *
   *   @method   __isset
   *   @param    string           $name
   *   @return   bool
   */
   public function __isset($name) : bool
   {
      if ($name[0] == '_')
      {
         return parent::__isset($name);
      } else {
         return array_key_exists($name, $this->ctrlList);
      }
   }

   /**
   *   un set global Variable in TController
   *
   *   @method   __unset
   *   @param    string           $name
   *   @return   void
   */
   public function __unset($name) : void
   {
      if ($name[0] == '_')
      {
         parent::__unset($name);
      } elseif (array_key_exists($name, $this->ctrlList)) {
         unset($this->ctrlList[$name]);
      } else {
         if (isDebug())
         {
            echo "TController ERROR: Variable ou method ($name) no found";
         }
      }
   }

   /**
    * get Error Code http w3.org
    *
    * @method getErrorCode()
    * @return integer
    */
   public function getErrorCode() : int
   {
      return TController::$errorCode;
   }

   /**
    * set Error Code http w3.org
    *
    * @method setErrorCode()
    * @param int $errorCode
    * @return void
    */
   public function setErrorCode(int $errorCode) : void
   {
      TController::$errorCode = $errorCode;
   }

   /**
   * Load Action
   * @method bool loadAction()
   * @param string $method
   * @param  mixed ...$params
   * @return mixed
   */
   protected function loadAction(string $method, ...$params)
   {
      if (method_exists($this, $method))
      {
         $refMethod = new ReflectionMethod($this->className, $method);
         if (($refMethod->isPublic())
         or ($method[0] != '_'))
         {
            if (!TController::$errorCode)
            {
               $attrib = TController::getAttribute($refMethod);
               if (TController::testPrivilege($attrib, $this->className.'->'.$method))
               {
                  $viewFile = array_key_exists('view',$attrib)?$attrib['view']:$method.'.html';
                  if ($viewFile[0] == '.')
                  {
                     $fileName = __BASEDIR__.substr($viewFile, 1);
                  } else {
                     $fileName = $this->pathLocation.'/_view/'.$viewFile;
                  }

                  if (file_exists($fileName))
                  {
                     $this->ctrlList['View'] = new TView();
                     $this->ctrlList['View']->loadFile($fileName);
                  }
                  return $refMethod->invokeArgs($this, $params);
               }
            }
         } else {
            if (isDebug())
            {
               echo "TController ERROR: Method ($this->className
                  ->$method) is not public or blocked";
            }
            TController::$errorCode = 403;
         }
      } else {
         if (isDebug())
         {
            echo "TController ERROR: Method ($this->className
               ->$method) don't found";
         }
         TController::$errorCode = 404;
      }
      return NULL;
   }

   /**
   *   set privilege Controller access Bitwise
   *
   *   @method   setPrivilege
   *   @param    int            $privilege
   */
   protected static function setPrivilege(int $privilege) : void
   {
      TController::$havePrivilege = $privilege;
   }

   /**
   *   add privilege Controller access Bitwise
   *
   *   @method   addPrivilege
   *   @param    int     $privilege
   */
   protected static function addPrivilege(int $privilege) : void
   {
      TController::$havePrivilege = TController::$havePrivilege | $privilege;
   }

   /**
   *   delete privilege controler access Bitwise
   *
   *   @method   delPrivilege
   *   @param    int     $privilege
   */
   protected static function delPrivilege(int $privilege) : void
   {
      TController::$havePrivilege = TController::$havePrivilege & ~$privilege;
   }

   /**
   *   test if you have the privilege Bitwise
   *
   *   @method   hasPrivilege
   *   @param    int           $needPrivilege
   *   @return   bool
   */
   public static function hasPrivilege(int $privilege) : bool
   {
      if (($privilege == 0)
      OR ((TController::$havePrivilege & $privilege) != 0))
      {
         return true;
      } else {
         return false;
      }
   }

   /**
    * Check the class or method attribute and collect
    * its definite permission, treat it as an error u
    * return an error if you do not have the necessary
    * permission to execute it.
    *
    * @param array $attrib
    * @param string $name
    * @return boolean
    */
   private static function testPrivilege(array $attrib, string $name): bool
   {
      if (array_key_exists('privilege', $attrib))
      {
         if (!TController::hasPrivilege($attrib['privilege']))
         {
            if (isDebug())
            {
               echo "TController ERROR: class or method: $name not have permission to access";
            }
            TController::$errorCode = 403;
            return false;
         }
      }
      return true;
   }

   /**
    *   load Controller and namespace respectives and
    *   execute last method "action"
    *   @method   autoLoad
    *   @param    string           $appPath
    *   @param    string           $cntrl
    *   @param    string           $action
    */
   public static function autoLoad(string $appPath, string $cntrl, string $action = '') : void
   {
      $pathArray = array();
      preg_match_all('/\w+/i', $cntrl, $pathArray);

      $cntrl = '';
      $ctrlArray = array();
      while ((!TController::$errorCode) AND ($pathArray[0]))
      {
         $ctrlArray[] =
            TController::loadController($appPath, $cntrl, $action, true, false, true);
         $cntrl .= '/'.array_shift($pathArray[0]);
      }

      TController::loadController($appPath, $cntrl, $action, true, true, true);

      while ($ctrlArray)
      {
         array_pop($ctrlArray);
      }
   }

   /**
    * load Controller and namespace and
    * execute method "action" if $toExecute is true
    *
    * @author	Franco Michel Almeida Caixeta
    * @access	public static
    * @param	string 	$appPath
    * @param	string 	$page
    * @param	string 	$action
    * @param	boolean	$toConstruct     Execute method Construct
    * @param	boolean	$toExecute       Execute method Action
    * @param	boolean	$toDestruct      Execute method Destruct
    * @param	mixed  	...$params       Send parameter method Action
    * @return	mixed                     method Action return or action not execute controller return
    */
   public static function loadController(string $appPath, string $page = '', string $action = '',
      bool $toConstruct = true, bool $toExecute = true, bool $toDestruct = true, ...$params)
   {
      $path = $appPath.$page;
      $fileCtrl = $path.'/controller.php';
      $namespace = str_replace( '/', '\\', $page);

      if (file_exists($fileCtrl) and require_once($fileCtrl))
      {
         $ctrlClass = $namespace.'\Controller';
         if (class_exists($ctrlClass))
         {
            $refCtrl = new ReflectionClass($ctrlClass);
            $attrib = TController::getAttribute($refCtrl);
            if (!TController::testPrivilege($attrib, $ctrlClass))
            {
               return NULL;
            }

            $controller = $refCtrl->newInstanceWithoutConstructor();
            $controller->className = $ctrlClass;
            $controller->classNamespace = $namespace;
            $controller->pathLocation = $path;
            $controller->toConstruct = $toConstruct;
            $controller->toExecute = $toExecute;
            $controller->toDestruct = $toDestruct;

            $fileModel = $path.'/model.php';
            if (file_exists($fileModel))
            {
               require_once($fileModel);
               if ($refCtrl->hasProperty('Model'))
               {
                  $modelClass = $controller->classNamespace.'\Model';
                  $Model = new ReflectionProperty($controller->className, 'Model');
                  $Model->setAccessible( true );
                  if (class_exists($modelClass))
                  {
                     $Model->setValue($controller, new $modelClass());
                  } else {
                     $Model->setValue($controller, new TModel());
                  }
               }
            }

            if ($toConstruct AND method_exists($controller, '__construct'))
            {
               $controller->loadAction('__construct');
            }

            if ($toExecute)
            {
               $action = ($action)?$action:'execute';
               return $controller->loadAction($action, ...$params);
            }
            return $controller;
         } else {
            if (isDebug())
            {
               echo "TController ERROR: Class ($ctrlClass) don't found";
            }
            TController::$errorCode = 404;
         }
      } else {
         if (isDebug())
   	   {
   	      echo "TController ERROR: File ($fileCtrl) don't found";
   	   }
         TController::$errorCode = 404;
      }
      return NULL;
   }

   /**
   *   get Attributes tag class or method.
   *
   *   @method   getAttribute
   *   @param    ReflectionClass|ReflectionMethod   $reflection
   *   @return   array
   */
   private static function getAttribute($reflection) : array
   {
      $pregResult = array();
      preg_match_all(
         '/@ATTRIBUTE +(\w+) *= *(.+?);/i',
         $reflection->getDocComment(),
         $pregResult
      );

      $result = array();
      if($pregResult)
      foreach ($pregResult[1] as $key => $value)
      {
         $result[$value] = $pregResult[2][$key];
      }

      return $result;
   }

}
?>
