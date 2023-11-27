<?php
namespace FMAC\MVC;
require_once(__DIR__.'/model/modelelement.php');
use FMAC\MVC\Model\TModelElement;
use Exception;

/**
 *   TModel
 *
 *   Class for connection and manipulation in the database
 */
class TModel extends TModelElement
{
   /**
   *   Define Variable List Model
   *   @var  array
   */
   private array $modelList = array();

   /**
   * constructor to prevent the parent constructor from calling
   */
   public function __construct()
   {
   }

   /**
    *   Construc TModel connected DataBase PDO
    *
    *   @method   connectDB
    *   @param    string   $fileName   INI File formatted
    */
   public static function connectDB(string $fileName, ?array $varList = NULL)
   {
		if (!file_exists($fileName))
		{
         throw new Exception(
            "TModel connectDB ERROR: File ($fileName) no found."
         );
      }

      $iniFile = parse_ini_file($fileName, true);
      foreach ($iniFile as $key => $value)
      {
         if (issets($value,'dsn'))
         {
            $dsn = $value['dsn'];
         }else{
            if (issets($value,'driver'))
            {
               $dsn = $value['driver'];
            }

            if (issets($value,'host'))
            {
               $dsn.=':host='.$value['host'];
               if ( array_key_exists('port',$value) )
               {
                  $dsn .= ';port='.$value['port'];
               }
            } elseif (issets($value,'server')) {
               $dsn.=':server='.$value['server'];
               if ( array_key_exists('port',$value) )
               {
                  $dsn .= ','.$value['port'];
               }
            }

            if ( array_key_exists('dbname',$value) )
            {
               $dsn .= ';dbname='.$value['dbname'];
            } elseif ( array_key_exists('database',$value) ) {
               $dsn .= ';database='.$value['database'];
            }

            if (array_key_exists('charset', $value)) {
               $dsn .= ';charset=' . $value['charset'];
            }
         }

         $dsn = replaceVariables($varList, $dsn);
         $user = replaceVariables($varList, $value['user']);
         $password = replaceVariables($varList, $value['password']);

         TModelElement::$ModelElementList[$key] = new TModelElement(
            $dsn,
            $user,
            $password
         );
      }
   }

   /**
   *   set global Variable in TModel
   *
   *   @method   __set
   *   @param    string           $name
   *   @param    mixed            $value
   *   @return   void
   */
   public function __set(string $name, $value) : void
   {
      if (array_key_exists($name,(array) TModelElement::$ModelElementList))
      {
         throw new Exception("TModel Error: no set $name, Element is exist.");
      } elseif (substr($name,0,1) == '_') {
         parent::__set($name, $value);
      } else {
         $this->modelList[$name] = $value;
      }
   }

   /**
   *   __get TModelElement
   *   @method   __get
   *   @param    string   $key
   *   @return   mixed
   */
   public function &__get(string $name)
   {
      if (array_key_exists($name,(array) TModelElement::$ModelElementList))
      {
         return TModelElement::$ModelElementList[$name];
      } elseif (substr($name,0,1) == '_') {
         return parent::__get($name);
      } elseif (array_key_exists($name, $this->modelList)) {
         return $this->modelList[$name];
      } else {
         if (isDebug())
         {
            echo "TModel ERROR: Variable ou method ($name) no found";
         }
         return NULL;
      }
   }

   /**
   *   is set global Variable in TModel
   *
   *   @method   __isset
   *   @param    string           $name
   *   @return   bool
   */
   public function __isset($name) : bool
   {
      if (array_key_exists($name,(array) TModelElement::$ModelElementList))
      {
         return true;
      } elseif (substr($name,0,1) == '_') {
         return parent::__isset($name);
      } else {
         return array_key_exists($name, $this->modelList);
      }
   }

   /**
   *   un set global Variable in TModel
   *
   *   @method   __unset
   *   @param    string           $name
   *   @return   void
   */
   public function __unset($name) : void
   {
      if  (array_key_exists($name,(array) TModelElement::$ModelElementList))
      {
         throw new Exception("TModel Error: dont unset $name.");
      } elseif (substr($name,0,1) == '_') {
         parent::__unset($name);
      } elseif (array_key_exists($name, $this->modelList)) {
         unset($this->modelList[$name]);
      } else {
         if (isDebug())
         {
            echo "TModel ERROR: Variable ou method ($name) no found";
         }
      }
   }
}
?>
