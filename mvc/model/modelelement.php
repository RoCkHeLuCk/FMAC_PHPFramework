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

   /**
    *   PDO Element
    *
    *   @var   PDO
    */
   private PDO $PDOElement;

   /**
    *   Error message
    *
    *   @var   string
    */
   private static string $errorMessage;

   /**
    *   Construc TModelElement connected DataBase PDO
    *
    *   @method   __construct
    *   @param    string      $dsn
    *   @param    string      $user
    *   @param    string      $pswrd
    */
   protected function __construct(string $dsn = '', string $user = '',
      string $pswrd = '')
   {
      try
      {
         $this->PDOElement = new PDO(
            $dsn,
            $user,
            $pswrd
         );
         //$conn = new PDO("sqlsrv:server=$serverName;Database=$database", $uid, $pwd);
         $this->PDOElement->setAttribute(
            3,//<-PDO::ATTR_ERRMODE,
            2,//<-PDO::ERRMODE_EXCEPTION
         );
         $this->PDOElement->setAttribute(
            1001,//<-PDO::SQLSRV_ATTR_QUERY_TIMEOUT,
            1,//<-PDO::SQLITE_OPEN_READONLY
         );
      } catch (PDOException $th) {
         TModelElement::$errorMessage = $th->getMessage();
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
      return TModelElement::$errorMessage;
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
      return (($result) AND ($result->rowCount()));
   }

   /**
    *   One occurrence query
    *
    *   @method   oneQuery
    *   @param    string    $sql
    *   @param    bool      $varDump
    *   @return   array     one level
    */
   public function oneQuery(string $sql, bool $varDump = false) : array
   {
      $result = $this->query($sql, $varDump);
      if ($result)
      {
         return reset($result);
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
   public function query(string $sql, bool $varDump = false,
      int $fetch = PDO::FETCH_ASSOC) : array
   {
      $result = $this->allQuery($sql, $varDump);
      if ($result)
      {
         return $result->fetchAll($fetch);
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
      $result = NULL;
      try
      {
         if ($varDump)
         {
            echo('<br/>SQL:');
            echo($sql);
            echo('<br/>');
         }
         $result = $this->getPDOElement()->query($sql);
         if ($varDump)
         {
            echo('<br/>Result:');
            var_dump($result);
            echo('<br/>');
         }
      } catch (PDOException $th) {
         TModelElement::$errorMessage = $th->getMessage();
         if (isDebug())
         {
            throw new Exception('TModel DB Query Error: '.$th->getMessage());
         }
      }
      return $result;
   }
}
?>
