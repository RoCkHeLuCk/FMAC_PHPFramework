<?php
namespace FMAC\__DEV;

class TEmail
{
   /**
    *   Define Email adminstrator
    *
    *   @var   string
    */
   private static string $ADMINMAIL = '';

   /**
    *   Define Subject Email ERROR
    *
    *   @var   string
    */
   private static string $SUBJECTMAIL = '';

   /**
    *   Set Admin email and subject error
    *
    *   @method   setAdminEmail
    *   @param    string          $adminEmail
    *   @param    string          $subject
    */
   protected function setAdminEmail(string $adminEmail, string $subject) : void
   {
      TBase::$ADMINMAIL = $adminEmail;
      TBase::$SUBJECTMAIL = $subject;
   }

   /**
    *   Sent to the adminstrator's email.
    *
    *   @method   ERROR
    *   @param    string   $message
    */
   protected function sendMail(string $message) : void
   {
      if ((TBase::$ADMINMAIL)AND(TBase::$SUBJECTMAIL))
      {
         $message = wordwrap($message, 70);
         mail(TBase::$ADMINMAIL, TBase::$SUBJECTMAIL, $message);
      }
   }
}
?>
