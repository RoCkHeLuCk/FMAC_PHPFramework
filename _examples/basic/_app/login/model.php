<?php
namespace Login;
use FMAC\MVC\TModel;

class Model extends TModel
{
   function hasEmail($email)
   {
      return $this->boolQuery(
         "SELECT email
          FROM client
          WHERE (email = lower('$email'));"
      );
   }

   function hasCXXX($cxxx)
   {
      return $this->boolQuery(
         "SELECT cxxx
          FROM client
          WHERE (cxxx = '$cxxx');"
      );
   }

   function insert($post)
   {
      $post['birth'] = empty($post['birth'])?'null':"'$post[birth]'";
      return $this->oneQuery(
         "INSERT INTO client (
            fullname, email, cxxx, pswrd, phone, birth
         )
         VALUES(
            '$post[fullname]',
            lower('$post[email]'),
            '$post[cxxx]',
            md5('$post[pswrdNew1]'),
            '$post[phone]',
            $post[birth]
         )
         RETURNING
            *;"
      );
   }

   function selectPswrdCXXX($post)
   {
      return $this->oneQuery(
         "SELECT *
          FROM client
          WHERE (
            cxxx = '$post[cxxx]'
            AND
            pswrd = md5('$post[pswrd]')
          );"
      );
   }

   function SelectEmailCXXX($post)
   {
      return $this->oneQuery(
         "SELECT *
          FROM client
          WHERE (
            cxxx = '$post[cxxx]'
            AND
            email = lower('$post[email]')
          );"
      );
   }

   function setValid(int $id, string $email) : bool
   {
      return $this->boolQuery(
         "UPDATE client SET
            email = lower('$email'),
            valid_email = true
          WHERE
            (id = $id);"
      );
   }

   function setReset($id, $pswrd)
   {
      return $this->boolQuery(
         "UPDATE client SET
            pswrd = md5('$pswrd')
          WHERE
            (id = $id);"
      );
   }

}
?>
