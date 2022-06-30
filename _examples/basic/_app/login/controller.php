<?php
namespace Login;
use FMAC\MVC\TController;
use function FMAC\Utils\validateCXXX;

class Controller extends TController
{
   public function __destruct()
   {
      $this->_index->main->insertOf($this->View);
   }

   public function execute()
   {
      $this->loadAction('login');
   }

   public function logout()
   {
      $_SESSION['client'] = array();
      header('Location: ./?pg=client/login&ac=login');
      exit;
   }

   public function login()
   {
      if (issets($_POST,'cxxx','pswrd','submit'))
      {
         $client = $this->Model->selectPswrdCXXX($_POST);
         if (!$client)
         {
            alertMsg($this->View, 'submit','LoginMsgError','error');
         }else{
            $_SESSION['client'] = array();
            $_SESSION['client'] = $client;
            header('Location: ./?pg=client');
            exit;
         }
      }
      jsLoader($this->_index,'cxxx&password');
   }

   public function register()
   {
      if (issets($_POST,'fullname','email','cxxx',
         'pswrdNew1','pswrdNew2','phone','birth','submit'))
      {
         $error = false;
         if(!preg_match('/'.$this->region->format['name'].'/',$_POST['fullname']))
         {
            alertMsg($this->View, 'fullname','FullnameMsgError','error');
            $error = true;
         }

         if(!preg_match('/'.$this->region->format['email'].'/',$_POST['email']))
         {
            alertMsg($this->View, 'email','InvalidMsgError','error');
            $error = true;
         }

         if($this->Model->hasEmail($_POST['email']))
         {
            alertMsg($this->View, 'email','EmailMsgAlready','error');
            $error = true;
         }

         if ( !validateCXXX($_POST['cxxx']) )
         {
            alertMsg($this->View, 'cxxx','InvalidMsgError','error');
            $error = true;
         }

         if ( $this->Model->hasCXXX($_POST['cxxx']) )
         {
            alertMsg($this->View, 'cxxx','CxxxMsgAlready','error');
            $error = true;
         }

         if (!preg_match('/'.$this->region->format['password'].'/',$_POST['pswrdNew1']))
         {
            alertMsg($this->View, 'pswrdNew1','PswrdNew1MsgError','error');
            $error = true;
         }

         if ($_POST['pswrdNew1'] != $_POST['pswrdNew2'])
         {
            alertMsg($this->View, 'pswrdNew2','PswrdNew2MsgError','error');
            $error = true;
         }

         if (!preg_match('/'.$this->region->format['phone'].'/',$_POST['phone']))
         {
            alertMsg($this->View, 'phone','PhoneMsgError','error');
            $error = true;
         }

         if (!$error)
         {
            $client = $this->Model->insert($_POST);
            if (!$client)
            {
               alertMsg($this->View, 'submit','LoginMsgError','error');
            }else{
               $_SESSION['client'] = array();
               $_SESSION['client'] = $client;
               header('Location: ./?pg=client');
               exit;
            }
         }
         $this->View->foreachElements($_POST);
      }
      jsLoader($this->_index,'cxxx&password&phone');
   }

   /**
    * @attribute privilege = 1;
    */
   public function validate()
   {
      if (issets($_POST,'email','code','submit'))
      {
         if (($_SESSION['clientEmail'] == $_POST['email'])
         and ($_SESSION['clientCode'] == $_POST['code']))
         {
            $this->Model->setValid(
               $_SESSION['client']['id'],
               $_SESSION['clientEmail']
            );
            unset($_SESSION['clientEmail']);
            unset($_SESSION['clientCode']);
            header('Location: ./?pg=client');
            exit;
         }else{
            alertMsg($this->View, 'code','ValidateMsgError','error');
         }
         $this->View->foreachElements($_POST);
      }

      if (!issets($_SESSION, 'clientCode')
      or issets($_POST,'email','resubmnit'))
      {
         if (issets($_POST,'email'))
         {
            $_SESSION['clientEmail'] = $_POST['email'];
         }else{
            $_SESSION['clientEmail'] = $_SESSION['client']['email'];
         }
         $_SESSION['clientCode'] =  rand ( 1000 , 9999 );
         if (!isDebug())
         {
            //enviar email?????????????????????
         }
      }

      if (isDebug())
      {
         echo $_SESSION['clientCode'];
      }

      if (issets($_SESSION,'clientEmail'))
      {
         $this->View->email->attribute('value')->set($_SESSION['clientEmail']);
      }else{
         unset($_SESSION['clientEmail']);
         unset($_SESSION['clientCode']);
         header('Location: ./?pg=client/login');
         exit;
      }
   }

   public function reset()
   {
      if (issets($_POST,'email','cxxx','submit'))
      {
         $client = $this->Model->SelectEmailCXXX($_POST);
         if (!$client)
         {
            alertMsg($this->View, 'submit','ResetMsgError','error');
         }else{
            $pswrd = strRand(8);
            if (isDebug())
            {
               echo $pswrd;
            } else {
               //enviar email ?????????????
            }
            $this->Model->setReset($client['id'],$pswrd);
            $this->View->loadFile(__DIR__.'/_view/resetmsg.html');
         }
      }
      jsLoader($this->_index,'cxxx');
   }
}

?>
