<?php
class Admin_Auth_Kbo extends Admin {
   public $db;
   public function ListGroups() {
      $this->db->q("SELECT DISTINCT GroupName FROM groups ORDER BY GroupName");
      $g = array();
      while ($i = $this->db->fetch_object()) {
         $g[] = $i->GroupName;
      }
      return $g;
   }

   public function SetGroups($user) {
      $this->db->q("SELECT GroupName FROM groups WHERE Member = '", $user->uid, "' and Language = '", $user->language, "'");
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

   public function StorePassword($password, $user = null) {
      // Store a password for the user.
      // Require: a password to set.
      // Optional: A user object to choose user.
      // Return: boolean
      
      if (is_null($user)) {
         $user = &$this;
      }
      
      $salt = "\$1\$";
      $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ./";
      $chars = str_shuffle($chars);
      for($i = 0; $i <= 8; $i++) {
         $c = rand(0,(strlen($chars) -1));
         $salt .= substr($chars, $c, 1);
      }
      $encpw = crypt($password, $salt);

      $this->db->q("
         UPDATE profiles
         SET    user_passwd = '", $encpw, "'
         WHERE  uid = ", $user->uid, "
         LIMIT 1
      ");
      if ($this->db->affected_rows() == 1) {
         return true;
      }
      else {
         return false;
      }
   }

   public function Test_Mail($mail) {
      $this->db->q("
         SELECT
            uid,
            user_name as username,
            user_passw as password,
            1 as active,
            first_name as firstname,
            last_name as surname,
            '", $this->default_languae, "' as language,
            email as email
         FROM profiles u
         WHERE u.email = '", $mail, "'
         LIMIT 1
      ");
      
      if ($this->db->num_rows() == 1) {
         return $this->db->fetch_object();
      }
      return false;
   }

   public function Test_UID($uid) {
      $this->db->q("
         SELECT
            uid,
            user_name as username,
            user_passw as password,
            1 as active,
            first_name as firstname,
            last_name as surname,
            '", $this->default_language, "' as language,
            email as email
         FROM profiles u
         WHERE u.uid = '", $uid, "'
         LIMIT 1
      ");
      if ($this->db->num_rows() == 1) {
         return $this->db->fetch_object();
      }
      else {
         return false;
      }
   }
   
   public function Test_User($username, $password) {
      $this->db->q("
         SELECT
            uid,
            user_name as username,
            user_passw as password,
            1 as active,
            first_name as firstname,
            last_name as surname,
            '", $this->default_language, "' as language,
            email as email
         FROM profiles u
         WHERE u.user_name = '", $username, "'
         LIMIT 1
      ");
      
      if ($this->db->num_rows() == 1) {
         $user = $this->db->fetch_object();
         $p = crypt($password, $user->password);
         //if ($user->active == "1") {
         //   $user->active = true;
         //}
         //else {
         //   $user->active = false;
         //}
         if ($p == $user->password) {
            return $user;
         }
      }
      else {
         return false;
      }
   }
   
   public function UpdateLastLogin($user = null) {
      if (is_null($user)) {
         $user = &$this;
      }
      $i = $user->uid;
      $this->db->q("UPDATE profiles SET last_login = NOW() WHERE uid = '", $i, "' limit 1");
      if ($this->db->affected_rows() == 1) {
         return true;
      }
      else {
         return false;
      }
   }
   
   private function ChangeLanguageUpdate($user, $language) {
      //$uid = $user->uid;
      //$this->db->q("update users set Language='", $language, "' where UserID='", $uid, "'");
   }

   /*
   public function AddUser($username, $options = null) {
      $this->db->q("SELECT UserID FROM users WHERE Username='", $username, "' and Type='native' and Erased=0");
      if ($this->db->num_rows()) {
         $dbData = $this->db->fetch_assoc();
         return $dbData['UserID'];
      }
      else {
         $email = $username;
         $firstname = $username;
         $surname = $username;
         $this->db->q("
            INSERT INTO users (Username, Mail, Firstname, Surname, Language, Type, Created) VALUES (
               '", $username, "',
               '", $email, "',
               '", $firstname, "',
               '", $surname, "',
               '", $this->default_language, "',
               'native',
               NOW()
            )
         ");
         return $this->db->insert_id();
      }
   }

   public function DeleteUser($uid) {
      $uid = (int) $uid;
      $this->db->q("UPDATE users SET Erased = NOW() WHERE UserID = $uid");
      return true;
   }
   */
}
?>
