<?php

// This class is to hold all user properties

define("TYPE_BOOLEAN", 1);
define("TYPE_INT", 2);
define("TYPE_STRING", 4);

class Admin_User {
   protected $active = true;
   protected $username;
   protected $password;
   protected $authModule;
   protected $firstname;
   protected $lastname;
   protected $uid = 0;
   protected $mail;
   protected $externalReference;

   protected $keys;
   
   public function __construct($moduleName, $params = null) {
      $this->setValidKeys();
      $this->setParam("authModule", $moduleName);
      if (is_object($params) || is_array($params)) {
         foreach($params as $key => $value) {
            $this->setParam($key, $value);
         }
      }
   }
   
   public function getAuthModule() {
      return $this->getParam("authModule");
   }

   public function getExternalReference() {
      return $this->getParam("externalReference");
   }

   public function getUid() {
      return $this->getParam("uid");
   }
   
   // Function to retrieve various params.
   public function getParam($key) {
      if (in_array($key, $this->keys)) {
         return $this->$key;
      }
      else {
         return false;
      }
   }
   
   public function isActive() {
      return $this->getParam("active");
   }
   
   // Function to set availale parameters and sanitize the data type/value.
   public function setParam($key, $value) {
      error_log("Key: $key Value: $value Type: " . $this->keys[$key]);
      if (in_array($key, $this->keys)) {
         $type = $this->keys[$key];
         
         if ($type == TYPE_BOOLEAN && is_bool($value)) {
            $this->$key = $value;
            error_log("Key: {$key} Value: {$this->$key} Type: " . $this->keys[$key]);
            return true;
         }
         
         elseif ($type == TYPE_BOOLEAN && is_string($value)) {
            switch (strtolower($value)) {
               case "yes":
               case "y":
               case "true":
               case "1":
                  $this->$key = true;
                  error_log("Key: {$key} Value: {$this->$key} Type: " . $this->keys[$key]);
                  return true;
               
               case "no":
               case "n":
               case "false":
               case "0":
                  $this->$key = false;
                  error_log("Key: {$key} Value: {$this->$key} Type: " . $this->keys[$key]);
                  return true;
               
               default:
                  $this->$key = (bool) $value;
                  error_log("Key: {$key} Value: {$this->$key} Type: " . $this->keys[$key]);
                  return true;;
            }
            $this->$key = $value;
            error_log("Key: {$key} Value: {$this->$key} Type: " . $this->keys[$key]);
            return true;
         }
         
         elseif ($type == TYPE_INT) {
            $this->$key = (int) $value;
            error_log("Key: {$key} Value: {$this->$key} Type: " . $this->keys[$key]);
            return true;
         }
         
         elseif ($type == TYPE_STRING) {
            $this->$key = (string) $value;
            error_log("Key: {$key} Value: {$this->$key} Type: " . $this->keys[$key]);
            return true;
         }
      }
      error_log("return false");
      return false;
   }
   
   public function getPassword() {
      return $this->getParam("password");
   }
      
   
   public function setPassword($password) {
      return $this->setParam("password", $password);
   }
   
   protected function setValidKeys() {
      $this->keys = array(
         "active" => TYPE_BOOLEAN,
         "username" => TYPE_STRING,
         "password" => TYPE_STRING,
         "authModule" => TYPE_STRING,
         "firstname" => TYPE_STRING,
         "lastname" => TYPE_STRING,
         "uid" => TYPE_INT,
         "mail" => TYPE_STRING,
         "externalReference" => TYPE_STRING
      );
   }

}
?>