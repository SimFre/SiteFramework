<?php
class Admin_Auth_Simple {
   private $users;
   private $uids;
   public $loginFieldUsername = "login_username";
   public $loginFieldPassword = "login_password";
   public $loginFieldRemember = "login_remember";

   public function __construct() {
      parent::__construct();
      $this->moduleName(__CLASS__);
      $this->users['default'] = new Admin_User($this->moduleName());
      $this->users['default']->setParam("username", "default");
      $this->users['default']->setParam("password", '$1$xxxxxxxxx$xxxxxxxxxxxxxxxxxxxxxx');
      $this->users['default']->setParam("externalReference",  1000);
      $this->users['default']->setParam("active", true);
      $this->users['default']->setParam("firstname", "John");
      $this->users['default']->setParam("surname", "Doe");
      $this->users['default']->setParam("email", "example@example.com");
      $this->users['default']->setParam("lastLogin", "2012-12-31 23:59:59");
      $this->uids[1000] = &$this->users['default'];
   }

   public function authenticate() {
      if (isset($_POST[$this->loginFieldUsername]) && isset($_POST[$this->loginFieldPassword])) {
         $username = $_POST[$this->loginFieldUsername];
         $password = $_POST[$this->loginFieldPassword];
         $user = $this->Test_User($username, $password);
         return $user;
      }
      else {
         return false;
      }
   }

   public function StorePassword($password, &$user) {
      // Store a password for the user.
      // Require: a password to set.
      // Optional: A user object to choose user.
      // Return: boolean

      $salt = "\$1\$";
      $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ./";
      $chars = str_shuffle($chars);
      for($i = 0; $i <= 8; $i++) {
         $c = rand(0,(strlen($chars) -1));
         $salt .= substr($chars, $c, 1);
      }
      $encpw = crypt($password, $salt);

      $exref = $user->getExternalReference();
      if (isset($this->uids[$exref]->password)) {
         $this->uids[$exref]->password = $encpw;
         return true;
      }
      else {
         return false;
      }
   }

   // List all available groups
   public function ListGroups() {
      return array();
   }
   
   public function SetGroups($user) {
      return 0;
   }
   
   public function Test_Mail($mail) {
      // Get user profile from e-mail address.
      foreach($this->users as $user) {
         if ($user->email == $mail) {
            return $user;
         }
      }
      return false;
   }

   public function Test_UID($externalReference) {
      if (isset($this->uids[$externalReference])) {
         $user = $this->uids[$externalReference];
         if ($user->isActive()) {
            return $u;
         }
      }
      return false;
   }

   public function Test_User($username, $password) {
      if (isset($this->users[$username])) {
         $user = $this->users[$username];
         $currentPassword = $user->getPassword();
         $pass = crypt($password, $currentPassword);
         if ($pass == $currentPassword) {
            return $user;
         }
      }
      return false;
   }
      
   public function UpdateLastLogin(&$user) {
      $i = $user->getExternalReference();
      $user->setParam("lastLogin", date("Y-m-d H:i:s"));
      $this->uids[$i] = $user;
      return true;
   }
   
   public function AddUser($username) {
      $uid = 0;
      return $uid;
   }

   public function DeleteUser($uid) {
      return true;
   }
}
?>