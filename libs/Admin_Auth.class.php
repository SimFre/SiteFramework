<?php

// The purpose of this module is to define how users are authenticated.
// Profile is to be kept separate from authentication plugin. This will
// allow for multiple sign in modes to work with the same profile.
// Example auth's are are OpenID, user/password, active director and
// Facebook Connect. Storage can be RAM (default), files, or database.

abstract class Admin_Auth {

   protected $authModuleName;
   protected $groups;
   function __construct() {
      $this->groups = Array();
      $this->moduleName(__CLASS__);
   }
   
   function __toString() {
      return $this->authModuleName;
   }

   // Set or retrieve authentication module name.
   // @optional String name
   // @return String name
   public function moduleName($name = null) {
      if ($name != null) {
         $this->authModuleName = $name;
      }
      return $this->authModuleName;
   }

   // List all available groups
   public function ListGroups() {
      return $this->groups;
   }
   
   // Do the actual authentication if none is currently present.
   // @return user object (Admin_User) or false
   abstract public function authenticate();
   
   // Fetch groups belonging to $user and set internal array.
   // @return int (number of groups)
   abstract public function SetGroups($user);
   
   // Store new password for current user or other user if given.
   // @return boolean
   abstract public function StorePassword($password, &$users);

   // Get user profile from e-mail address.
   // @return false or user-object.
   abstract public function Test_Mail($mail);

   // Get user profile from user ID number.
   // @return false or user-object.
   abstract public function Test_UID($uid);

   // Get user profile from username and password.
   // @return false or user-object.
   //abstract public function Test_User($username, $password);
   
   // Set last login tiestamp for user.
   // @return boolean
   abstract public function UpdateLastLogin(&$user);
   
   // Add user account
   // @return user ID number (int).
   abstract public function AddUser($username);

   // Delete user account
   // @return boolean
   abstract public function DeleteUser($uid);
   
   // Change language on user account.
   // @return void
   //abstract private function ChangeLanguageUpdate(&$user, $language);
   function ChangeLanguageUpdate($user, $language) { return; }
}

?>