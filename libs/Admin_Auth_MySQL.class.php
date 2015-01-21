<?php
class Admin_Auth_MySQL extends Admin_Auth {
   protected $db;
   public $loginFieldUsername = "login_username";
   public $loginFieldPassword = "login_password";
   public $loginFieldRemember = "login_remember";

   public function __construct(&$db) {
      parent::__construct();
      $this->moduleName(__CLASS__);
      $this->db = &$db;
   }

   public function ListGroups() {
      $this->db->q("SELECT DISTINCT GroupName FROM groups ORDER BY GroupName");
      $g = array();
      while ($i = $this->db->fetch_object()) {
         $g[] = $i->GroupName;
      }
      return $g;
   }

   public function SetGroups($user) {
      $this->db->q("SELECT GroupName FROM groups WHERE Member = '", $user->getExternalReference(), "' and Language = 'xx_XX'");
      if ($this->db->num_rows() > 0) {
         while($g = $this->db->fetch_assoc()) {
            $this->groups[] = $g['GroupName'];
         }
         return $this->db->num_rows();
      }
      else {
         return 0;
      }
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
      
      $this->db->q("
         UPDATE users
         SET    Password = '", $encpw, "', Modified = NOW()
         WHERE  UserID = ", $user->getExternalReference(), "
         LIMIT 1
      ");
      
      if ($this->db->affected_rows() == 1) {
         $user->setPassword($encpw);
         return true;
      }
      else {
         return false;
      }
   }

   public function Test_Mail($mail) {
      $this->db->q("
         SELECT
            UserID as externalReference,
            Username as username,
            Password as password,
            Firstname as firstname,
            Surname as surname,
            Mail as mail,
            IF(Active = 'Yes', 1, 0) as active
         FROM users u
         WHERE u.Mail = '", $mail, "' AND Erased is null
         LIMIT 1
      ");
      
      if ($this->db->num_rows() == 1) {
         $user = $this->db->fetch_object();
         return new Admin_User($this->moduleName(), $user);
      }
      return false;
   }

   public function Test_UID($externalReference) {
      $this->db->q("
         SELECT
            UserID as externalReference,
            Username as username,
            Password as password,
            Firstname as firstname,
            Surname as surname,
            Mail as mail,
            IF(Active = 'Yes', 1, 0) as active
         FROM users u
         WHERE u.UserID = '", $externalReference, "' AND Erased is null
         LIMIT 1
      ");
      if ($this->db->num_rows() == 1) {
         $user = $this->db->fetch_object();
         return new Admin_User($this->moduleName(), $user);
      }
      return false;
   }
   
   public function Test_User($username, $password) {
      $this->db->q("
         SELECT
            UserID as externalReference,
            Username as username,
            Password as password,
            Firstname as firstname,
            Surname as surname,
            Mail as mail,
            IF(Active = 'Yes', 1, 0) as active
         FROM users u
         WHERE u.Username = '", $username, "' AND Erased is null
         LIMIT 1
      ") or Log::d($this->db->error());
      
      if ($this->db->num_rows() == 1) {
         $user = $this->db->fetch_object();
         $currentPassword = $user->password;
         $p = crypt($password, $currentPassword);
         //error_log(var_export($user, true));
         //error_log("PW: $p, Cur: $currentPassword");
         if ($p == $currentPassword) {
            return new Admin_User($this->moduleName(), $user);
         }
      }
      return false;
   }
   
   public function UpdateLastLogin(&$user) {
      $i = $user->getExternalReference();
      $user->setParam("lastLogin", date("Y-m-d H:i:s"));
      $this->db->q("UPDATE users SET LastIP = '", $_SERVER['REMOTE_ADDR'], "', LastLogin = NOW() WHERE UserID = '", $i, "' limit 1")
         or Log::d($this->db->error());
      
      if ($this->db->affected_rows() == 1) {
         return true;
      }
      else {
         return false;
      }
   }
   
   public function AddUser($username, $options = null) {
      $this->db->q("SELECT UserID FROM users WHERE Username='", $username, "' and Erased is null");
      if ($this->db->num_rows()) {
         $dbData = $this->db->fetch_assoc();
         return $dbData['UserID'];
      }
      else {
         $email = $username;
         $firstname = $username;
         $surname = $username;
         $this->db->q("
            INSERT INTO users (Username, Mail, Firstname, Surname, Type, Created) VALUES (
               '", $username, "',
               '", $email, "',
               '", $firstname, "',
               '", $surname, "',
               'native',
               NOW()
            )
         ");
         return $this->db->insert_id();
      }
   }

   public function DeleteUser($externalReference) {
      $externalReference = (int) $externalReference;
      $this->db->q("UPDATE users SET Erased = NOW() WHERE UserID = $externalReference");
      return true;
   }

}
?>