<?php
namespace FMAC\MVC\Model;
require_once(__DIR__.'/../kernel/kernel.php');
use FMAC\MVC\Kernel\TKernel;
use Exception;
use PDO;
use PDOStatement;
use PDOException;

/**
 *  TModelElement conncect database PDO.
 */
class TModelElement extends TKernel
{
   /**
    *   List Data Base connection
    *
    *   @var   TModelElement
    */
   protected static array $ModelElementList = array();

   protected static bool $oldPrepare = false;

   private static string $sqlBeforePrepare = '';

   private static string $sqlAfterPrepare = '';

   private string $errorMessage;

   /**
    *   PDO Element
    *
    *   @var   PDO
    */
   private PDO $PDOElement;

   /**
    *   Construct TModelElement connected DataBase PDO
    *
    *   @method   __construct
    *   @param    string      $dsn
    *   @param    string      $user
    *   @param    string      $pswrd
    */
   protected function __construct(string $dsn = '', string $user = '', string $pswrd = '')
   {
      try
      {
         $this->PDOElement = new PDO(
            $dsn,
            $user,
            $pswrd
         );

         $this->PDOElement->setAttribute(
            3,//<-PDO::ATTR_ERRMODE,
            2 //<-PDO::ERRMODE_EXCEPTION
         );
         $this->PDOElement->setAttribute(
            1001,//<-PDO::SQLSRV_ATTR_QUERY_TIMEOUT,
            1 //<-PDO::SQLITE_OPEN_READONLY
         );

         $this->PDOElement->setAttribute(
            20, //<-PDO::ATTR_EMULATE_PREPARES
            false
         );

         $this->PDOElement->setAttribute(
            11, //<-PDO::ATTR_ORACLE_NULLS
            0 //<-PDO::NULL_NATURAL
         );

      } catch (PDOException $th) {
         $this->errorMessage = $th->getMessage();
         if (isDebug())
         {
            throw new Exception('TModel DB Connection Error: '.$th->getMessage());
         }
      }
   }

   /**
    *  get PDO Element
    *
    *   @method   getPDOElement
    *   @return   PDO
    */
   public function getPDOElement() : PDO
   {
      if (!isset($this->PDOElement))
      {
         $this->PDOElement = reset(TModelElement::$ModelElementList)
            ->getPDOElement();
      }
      return $this->PDOElement;
   }

   /**
    *  get Error Messages
    *
    *   @method   getErrorMessage
    *   @return   string
    */
   public function getErrorMessage() : string
   {
      return $this->errorMessage;
   }

   /**
    *   Bool query
    *
    *   @method   boolQuery
    *   @param    string    $sql
    *   @param    bool      $varDump
    *   @return   bool
    */
   public function boolQuery(string $sql, bool $varDump = false) : bool
   {
      $result = $this->allQuery($sql,$varDump);
      $result = (($result) AND ($result->rowCount()));
      if ($varDump AND isDebug())
      {
         echo('<br/>RESULT: '.$result?'true':'false'.'<br/>');
      }
      return $result;
   }


   /**
    * Execute sql and get last id
    *
    * @param string      $sql
    * @param string|null $row
    * @param boolean     $varDump
    *
    * @return integer
    */
   public function lastIdQuery(string $sql, ?string $row = NULL, bool $varDump = false) : int
   {
      $this->boolQuery($sql, $varDump);
      $result = $this->getPDOElement()->lastInsertId($row);
      if ($varDump AND isDebug())
      {
         echo('<br/>RESULT ['.$row.']: '.$result.'<br/>');
      }
      return $result;
   }

   /**
    *   One occurrence query
    *
    *   @method   oneQuery
    *   @param    string       $sql
    *   @param    string|null  $row
    *   @param    bool         $varDump
    *   @return   array|string Array one level or string Row
    */
   public function oneQuery(string $sql, ?string $row = NULL, bool $varDump = false)
   {
      $result = $this->query($sql, $varDump);
      if ($result)
      {
         $result = reset($result);
         if ($row AND isset($result,$row))
         {
            $result = $result[$row];
         }

         if ($varDump AND isDebug())
         {
            echo('<br/>RESULT:<pre>');
            print_r($result);
            echo('</pre><br/>');
         }
         return $result;
      } else
         return array();
   }

   /**
    *   All occurrence query
    *
    *   @method   allQuery
    *   @param    string    $sql
    *   @param    bool      $varDump
    *   @param    int       $fetch
    *   @return   array
    */
   public function query(string $sql, bool $varDump = false, int $fetch = PDO::FETCH_ASSOC) : array
   {
      $result = $this->allQuery($sql, $varDump);
      if ($result)
      {
         $result = $result->fetchAll($fetch);
         if ($varDump AND isDebug())
         {
            echo('<br/>RESULT:<pre>');
            print_r($result);
            echo('</pre><br/>');
         }
         return $result;
      }
      return array();
   }

   /**
    *   query
    *   @method   query
    *   @param    string    $sql
    *   @param    bool      $varDump
    *   @return   PDOStatement
    */
   private function allQuery(string $sql, bool $varDump = false) : ?PDOStatement
   {
      if ((strlen($sql) < 512) and file_exists($sql)) {
         $sql = file_get_contents($sql);
      }

      $result = NULL;
      try
      {
         if ($varDump AND isDebug())
         {
            echo('<br/>SQL:<pre>');
            echo($sql);
            echo('</pre><br/>');
         }
         $result = $this->getPDOElement()->query($sql);
      } catch (PDOException $th) {
         $this->errorMessage = $th->getMessage();
         if (isDebug())
         {
            throw new Exception('TModel DB Query Error: '.$th->getMessage());
         }
      }
      return $result;
   }

   /**
    * Set SQL concat Before and After Prepare
    *
    * @param  string $sqlBefore
    * @param  string $sqlAfter
    *
    * @return void
    */
   protected function setSQLWeddingPrepare(string $sqlBefore = '', string $sqlAfter = '')
   {
      TModelElement::$sqlBeforePrepare = ($sqlBefore)?$sqlBefore."\n":'';
      TModelElement::$sqlAfterPrepare = ($sqlAfter)?"\n".$sqlAfter:'';
   }

   /**
    *   Bool Prepare
    *
    *   @param    string    $sql
    *   @param    array     $keys
    *   @param    bool      $varDump
    *   @return   bool
    *
    *   @description
    *
    * Sql prepare {name} or {name:X} or {name:XY} or {[\*?]name[\*?]:XY}
    *
    * **X Convert variable to:
    * > 1. [Empty or A] -> Auto;
    * > 2. [L] -> List (implode ',');
    * > 3. [S] -> String;
    * > 4. [N] -> Number;
    * > 5. [\*?]-> Use string only, SQL Convert '*' to '%'  and  '?' to '_';
    *
    * **Y Convert 0 or Null to:
    * > 1. [Empty or A]
    * > +   list:  0 = 0    | '' = ''   | NULL = NULL
    * > + string:  0 = 0    | '' = ''   | NULL = NULL
    * > + number:  0 = 0    | '' = 0    | NULL = NULL
    * > 2. [E]
    * > +   list:  0 = 0    | '' = ''   | NULL = 0
    * > + string:  0 = 0    | '' = ''   | NULL = ''
    * > + number:  0 = 0    | '' = 0    | NULL = 0
    * > 3. [N]
    * > +   list:  0 = NULL | '' = NULL | NULL = NULL
    * > + string:  0 = NULL | '' = NULL | NULL = NULL
    * > + number:  0 = NULL | '' = NULL | NULL = NULL
    *
    */
   public function boolPrepare(string $sql, $keys = NULL, bool $varDump = false) : bool
   {
      $result = $this->allPrepare($sql, $keys, $varDump);
      if ($varDump AND isDebug())
      {
         if ($result)
         {
            echo('<br/>RESULT: true<br/>');
         } else {
            echo('<br/>RESULT: false<br/>');
         }
      }
      return $result?true:false;
   }

   /**
    *   Execute sql and get last id
    *
    *   @param string      $sql
    *   @param array     $keys
    *   @param string|null $row
    *   @param boolean     $varDump
    *
    *   @return integer
    *
    *   @description
    *
    * Sql prepare {name} or {name:X} or {name:XY} or {[\*?]name[\*?]:XY}
    *
    * **X Convert variable to:
    * > 1. [Empty or A] -> Auto;
    * > 2. [L] -> List (implode ',');
    * > 3. [S] -> String;
    * > 4. [N] -> Number;
    * > 5. [\*?]-> Use string only, SQL Convert '*' to '%'  and  '?' to '_';
    *
    * **Y Convert 0 or Null to:
    * > 1. [Empty or A]
    * > +   list:  0 = 0    | '' = ''   | NULL = NULL
    * > + string:  0 = 0    | '' = ''   | NULL = NULL
    * > + number:  0 = 0    | '' = 0    | NULL = NULL
    * > 2. [E]
    * > +   list:  0 = 0    | '' = ''   | NULL = 0
    * > + string:  0 = 0    | '' = ''   | NULL = ''
    * > + number:  0 = 0    | '' = 0    | NULL = 0
    * > 3. [N]
    * > +   list:  0 = NULL | '' = NULL | NULL = NULL
    * > + string:  0 = NULL | '' = NULL | NULL = NULL
    * > + number:  0 = NULL | '' = NULL | NULL = NULL
    *
    */
   public function lastIdPrepare(string $sql, $keys = NULL, ?string $row = NULL, bool $varDump = false) : int
   {
      $result = 0;
      if ($this->boolPrepare($sql, $keys, $varDump))
      {
         $result = $this->getPDOElement()->lastInsertId($row);
      }
      if ($varDump AND isDebug())
      {
         echo('<br/>RESULT ['.$row.']: '.$result.'<br/>');
      }
      return $result;
   }


   /**
    *   One occurrence prepare
    *
    *   @param    string       $sql
    *   @param    array        $keys
    *   @param    string|null  $row
    *   @param    bool         $varDump
    *   @return   array|string Array one level or string Row
    *
    *   @description
    *
    * Sql prepare {name} or {name:X} or {name:XY} or {[\*?]name[\*?]:XY}
    *
    * **X Convert variable to:
    * > 1. [Empty or A] -> Auto;
    * > 2. [L] -> List (implode ',');
    * > 3. [S] -> String;
    * > 4. [N] -> Number;
    * > 5. [\*?]-> Use string only, SQL Convert '*' to '%'  and  '?' to '_';
    *
    * **Y Convert 0 or Null to:
    * > 1. [Empty or A]
    * > +   list:  0 = 0    | '' = ''   | NULL = NULL
    * > + string:  0 = 0    | '' = ''   | NULL = NULL
    * > + number:  0 = 0    | '' = 0    | NULL = NULL
    * > 2. [E]
    * > +   list:  0 = 0    | '' = ''   | NULL = 0
    * > + string:  0 = 0    | '' = ''   | NULL = ''
    * > + number:  0 = 0    | '' = 0    | NULL = 0
    * > 3. [N]
    * > +   list:  0 = NULL | '' = NULL | NULL = NULL
    * > + string:  0 = NULL | '' = NULL | NULL = NULL
    * > + number:  0 = NULL | '' = NULL | NULL = NULL
    *
    */
   public function onePrepare(string $sql, $keys = NULL, ?string $row = NULL, bool $varDump = false)
   {
      $result = $this->prepare($sql, $keys, $varDump);
      if ($result)
      {
         $result = reset($result);
         if ($row AND isset($result,$row))
         {
            $result = $result[$row];
         }

         if ($varDump AND isDebug())
         {
            echo('<br/>RESULT:<pre>');
            print_r($result);
            echo('</pre><br/>');
         }
         return $result;
      } else {
         if ($row)
         {
            return NULL;
         } else {
            return array();
         }
      }
   }


   /**
    *   All occurrence query
    *
    *   @param    string    $sql
    *   @param    array     $keys
    *   @param    bool      $varDump
    *   @param    int       $fetch
    *   @return   array
    *
    *   @description
    *
    * Sql prepare {name} or {name:X} or {name:XY} or {[\*?]name[\*?]:XY}
    *
    * **X Convert variable to:
    * > 1. [Empty or A] -> Auto;
    * > 2. [L] -> List (implode ',');
    * > 3. [S] -> String;
    * > 4. [N] -> Number;
    * > 5. [\*?]-> Use string only, SQL Convert '*' to '%'  and  '?' to '_';
    *
    * **Y Convert 0 or Null to:
    * > 1. [Empty or A]
    * > +   list:  0 = 0    | '' = ''   | NULL = NULL
    * > + string:  0 = 0    | '' = ''   | NULL = NULL
    * > + number:  0 = 0    | '' = 0    | NULL = NULL
    * > 2. [E]
    * > +   list:  0 = 0    | '' = ''   | NULL = 0
    * > + string:  0 = 0    | '' = ''   | NULL = ''
    * > + number:  0 = 0    | '' = 0    | NULL = 0
    * > 3. [N]
    * > +   list:  0 = NULL | '' = NULL | NULL = NULL
    * > + string:  0 = NULL | '' = NULL | NULL = NULL
    * > + number:  0 = NULL | '' = NULL | NULL = NULL
    *
    */
   public function prepare(string $sql, $keys = NULL, bool $varDump = false, int $fetch = PDO::FETCH_ASSOC) : array
   {
      $result = $this->allPrepare($sql, $keys, $varDump);
      if ($result)
      {
         $result = $result->fetchAll($fetch);
         return $result;
      }
      return array();
   }

   /**
    *   all Prepare
    *   @method   allPrepare
    *   @param    string    $sql
    *   @param    array     $keys
    *   @param    bool      $varDump
    *   @return   PDOStatement
    */
   private function allPrepare(string $sql, $keys = NULL, bool $varDump = false)
   {
      if ((strlen($sql) < 512) and file_exists($sql)) {
         $sql = file_get_contents($sql);
      }
      $sql = TModelElement::$sqlBeforePrepare . $sql . TModelElement::$sqlAfterPrepare;

      if(TModelElement::$oldPrepare)
      {
         return $this->oldPrepare($sql, $keys, $varDump);
      } else {
         return $this->newPrepare($sql, $keys, $varDump);
      }
   }

   /**
    *   old Prepare
    *   @method   oldPrepare
    *   @param    string    $sql
    *   @param    array     $keys
    *   @param    bool      $varDump
    *   @return   PDOStatement
    */
   private function oldPrepare(string $sql, $keys = NULL, bool $varDump = false)
   {
      try
      {
         $binds = array();
         $sql = preg_replace_callback(
            '/:[%_]?([a-z]+)[%_]?/i',
            function ($match) use (&$binds, $keys)
            {
               if (!array_key_exists($match[1], $binds))
               {
                  if (is_array($keys))
                  {
                     $value = '';
                     if (array_key_exists($match[1], $keys))
                     {
                        $value = $keys[$match[1]];
                     } else {
                        throw new Exception('TModel DB Prepare bind "'.$match[1].'" no found');
                     }
                  } else {
                     $value = $keys;
                  }
                  $old = substr($match[0], 1, NULL);
                  $value = str_replace($match[1], $value, $old);
                  $binds[$match[1]] = $value;
               }
               return ':'.$match[1];
            },
            $sql
         );

         if ($varDump AND isDebug()) {
            echo ('<br/>SQL:<pre>');
            echo ($sql);
            echo ('</pre><br/>');
            echo ('Binds:<pre>');
            print_r($binds);
            echo ('</pre><br/>');
         }

         $statement = $this->getPDOElement()->prepare($sql);
         if ($statement->execute($binds))
         {
            $result = $statement;
         } else {
            $result = false;
         }

         if ($varDump and isDebug()) {
            echo ('<br/>RESULT:<pre>');
            print_r($result);
            echo ('</pre><br/>');
         }
      } catch (PDOException $th) {
         $this->errorMessage = $th->getMessage();
         if (isDebug())
         {
            throw new Exception('TModel DB Query Error: '.$th->getMessage());
         }
      }
      return $result;
   }

   /**
    *   new Prepare
    *   @method   newPrepare
    *   @param    string    $sql
    *   @param    array     $keys
    *   @param    bool      $varDump
    *   @return   PDOStatement
    *
    *   Sql prepare {name} or {name:X} or {name:XY} or {[*?]name[*?]:XY}
    *
    *   X Convert variable to:
    *      [Empty or A] -> Auto;
    *      [L] -> List (implode ',');
    *      [S] -> String; ==> Convert string only: "*" => "%" AND "?" => "_";
    *      [N] -> Number;
    *
    *   Y Convert 0 or Null to:
    *      [Empty or A]
    *           list:  0 = 0    |   '' = ''   | NULL = NULL
    *         string:  0 = 0    |   '' = ''   | NULL = NULL
    *         number:  0 = 0    |   '' = 0    | NULL = NULL
    *      [E]
    *           list:  0 = 0    |   '' = ''   | NULL = 0
    *         string:  0 = 0    |   '' = ''   | NULL = ''
    *         number   0 = 0    |   '' = 0    | NULL = 0
    *      [N]
    *           list:  0 = NULL |   '' = NULL | NULL = NULL
    *         string:  0 = NULL |   '' = NULL | NULL = NULL
    *         number:  0 = NULL |   '' = NULL | NULL = NULL
    */
   private function newPrepare(string $sql, $keys = NULL, bool $varDump = false)
   {
      $sql = preg_replace_callback(
         '/{([*?]?(\w+)[*?]?)(?:\:([alns])([aen])?)?}/i',
         function ($match) use ($keys) {

            if (is_array($keys)) {
               $result = NULL;
               if (array_key_exists($match[2], $keys)) {
                  $result = $keys[$match[2]];
               } else {
                  if (isDebug()) {
                     throw new Exception('TModel DB Prepare bind "' . $match[1] . '" no found');
                  }
               }
            } else {
               $result = $keys;
            }

            //A -> Auto
            //S -> String
            //N -> Number
            //L -> List
            $type = (isset($match[3]))?strtolower($match[3]):'a';
            if ($type == 'a') {
               $type = is_array($result)?'l':((gettype($result) == 'string')?'s':'n');
            }
            $fill = (isset($match[4]))?strtolower($match[4]):'a';

            switch ($type)
            {
               case 'l':{
                  if (is_array($result) and $result)
                  {
                     switch ($fill) {
                        // E  String
                        case 'e': {
                           foreach ($result as &$value)
                           {
                              $value = "'".$value."'";
                           }
                        } break;
                        // N  Number
                        case 'n': {
                           foreach ($result as &$value)
                           {
                              $value = +$value;
                           }
                        } break;
                     }
                     $result = implode(',', $result);
                  }

                  if (!$result)
                  {
                     $result = 'NULL';
                  }
               } break;

               case 'n': {
                  switch ($fill) {
                     // E  0 = 0    |   '' = 0    | NULL = 0
                     case 'e':{
                        if (!$result)
                        {
                           $result = 0;
                        } else {
                           $result = +$result;
                        }
                     } break;
                     // N  0 = NULL |   '' = NULL | NULL = NULL
                     case 'n':{
                        if (!$result)
                        {
                           $result = 'NULL';
                        } else {
                           $result = +$result;
                        }
                     } break;
                     // A  0 = 0    |   '' = 0    | NULL = NULL
                     case 'a':
                     default:{
                        if ($result === NULL)
                        {
                           $result = 'NULL';
                        } elseif ($result === '') {
                           $result = 0;
                        } else {
                           $result = +$result;
                        }
                     } break;
                  }
               } break;

               case 's':
               default:{
                  if ($match[1] != $match[2])
                  {
                     $result = str_replace(
                        ['*', '?', $match[2]],
                        ['%', '_', strval($result)],
                        $match[1]
                     );
                  }
                  //prevent chars
                  $result = addslashes($result.'');
                  switch ($fill) {
                     // E  0 = '0'  |   '' = ''   | NULL = ''
                     case 'e':{
                        $result = "'".$result."'";
                     } break;
                     // N  0 = NULL |   '' = NULL | NULL = NULL
                     case 'n':{
                        if (!$result)
                        {
                           $result = 'NULL';
                        } else {
                           $result = "'".$result."'";
                        }
                     } break;
                     // A  0 = '0'  |   '' = ''   | NULL = NULL
                     case 'a':
                     default:{
                        if ($result === NULL)
                        {
                           $result = 'NULL';
                        } elseif ($result === '') {
                           $result = "''";
                        } else {
                           $result = "'".$result."'";
                        }
                     } break;
                  }
               } break;
            }
            return $result;
         },
         $sql
      );

      return $this->allQuery($sql, $varDump);
   }

}
?>
