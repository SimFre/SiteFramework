<?php

// The purpose of this module is to define how user profile is stored.
// Profile is to be kept separate from authentication plugin. This will
// allow for multiple sign in modes to work with the same profile.
// Example auth's are are OpenID, user/password, active director and
// Facebook Connect. Storage can be RAM (default), files, or database.

abstract class Admin_Storage {

   private $users;

   function __constructor() {
      $this->users['default'] = new stdClass();
      $this->users['default']->username = 'default';
      $this->users['default']->password = '$1$xxxxxxxxx$xxxxxxxxxxxxxxxxxxxxxx';
      $this->users['default']->uid      = 1000;
      $this->users['default']->active   = true;
      $this->users['default']->language = 'xx_XX';
      $this->users['default']->firstname = 'John';
      $this->users['default']->surname = 'Doe';
      $this->users['default']->email = "example@example.com";
      $this->users['default']->lastLogin = "2012-12-31 23:59:59";
      $this->uids[1000] = &$this->users['default'];   
   
   }

}

?>