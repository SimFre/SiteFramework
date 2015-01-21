<?php
class Admin {  
   //public $uid = 0;
   public $groups = Array();
   
   // Make sure that the timeout is within the confines of session expiry and garbage collection.
   public $timeout = 900;
   
   public $login = false; //= "/admin/login.php";
   public $return = "/admin/index.php";
   public $username = "";
   public $groupError = "/admin/groupFail.php";
   public $default_language = "xx_XX";
   public $validateSameIP = false;

   public $loginFieldModule = "login_module";
   public $loginFieldRemember = "login_remember";
   public $loginCookiePath = "/";
   public $loginSessionBase = "Admin";
   protected $loggedIn = false;
   protected $errorReason = null;
   protected $authProcessHasRun = false;
   protected $authModules;
   protected $defaultAuthModule;
   
   public $profile;
   public $profileId = 0;

   public function __construct() {
      $this->authModules = Array();
      if(session_id() == "") {
         session_start();
      }
   }

   public function __toString() {
      return __CLASS__;
   }

   public function addAuthModule(&$authModule) {
      $name = $authModule->moduleName();
      $this->authModules[$name] = $authModule;
      if (count($this->authModules) == 1) {
         $this->defaultAuthModule = $name;
      }
   }

   public function RequireLogin() {
      $this->AutomaticAuth();
      if (!$this->ok()) {
         $reason = "";
         if (!is_null($this->errorReason) && strlen($this->errorReason) > 0) {
            $reason = "?reason=" . urlencode($this->errorReason);
         }
         header("HTTP/1.0 401 Unauthorized");
         header("Location: " . $this->login . $reason);
         exit();
      }
   }
   
   public function AutomaticAuth() {
      //error_log("autoatuth");
      //sleep(1); // Prevent brute force attacks
      $error = 999;
      $errorMessage = "Undefined login error";
      if ($this->authProcessHasRun) {
         return;
      }
      else {
         $this->authProcessHasRun = true;
      }
      
      if (!isset($_SESSION[$this->loginSessionBase]['ip']) || !isset($_SESSION[$this->loginSessionBase]['timestamp'])) {
         $error = 1;
         $errorMessage = "IP or timestamp not registered.";
         $_SESSION[$this->loginSessionBase]['errno'] = $error;
      }

      // Validating IP is important, but when multi-homing between IPv4 and IPv6
      // that tends to swap while viewing the site this becomes a problem.
      elseif ($this->validateSameIP && $_SESSION[$this->loginSessionBase]['ip'] != $_SERVER['REMOTE_ADDR']) {
         $error = 2;
         $errorMessage = "IP-address does not match logged in session.";
         $_SESSION[$this->loginSessionBase]['errno'] = $error;
      }

      elseif (!isset($_SESSION[$this->loginSessionBase]['externalReference'])) {
         $error = 3;
         $errorMessage = "externalReference not registered in session.";
         $_SESSION[$this->loginSessionBase]['errno'] = $error;
      }

      elseif (time() - $this->timeout > $_SESSION[$this->loginSessionBase]['timestamp']) {
         $error = 4;
         $errorMessage = "Login has expired.";
         $_SESSION[$this->loginSessionBase]['errno'] = $error;
      }
      
      elseif (!isset($_SESSION[$this->loginSessionBase]['moduleName'])) {
         $error = 5;
         $errorMessage = "No authentication module used.";
      }
      
      elseif (count($this->authModules) < 1) {
         $error = 6;
         $errorMessage = "No authentication module loaded.";
         $this->Fail($errorMessage);
         return;
      }
      
      else {
         $error = 0;
         $errorMessage = "No error.";
         $_SESSION[$this->loginSessionBase]['errno'] = $error;
      }
      
      if ($error > 0) {
         $m = $this->getDesiredModule();
         $user = $this->authModules[$m]->authenticate();
         //error_log("AuthMod: $m");
         //error_log(var_export($user, true));
         //error_log("Active: " . $user->isActive());
         if ($user !== false && $user->isActive()) {
            Log::d("Authentication success!");
            Log::d($user);
            $this->Success($user);
         }
         else {
            Log::d("Authentication failure!");
            Log::d("Module: $m, Error: $error");
            $this->Fail("Bad credentials");
         }

      }
      else {
         $externalReference = $_SESSION[$this->loginSessionBase]['externalReference'];
         $m = $_SESSION[$this->loginSessionBase]['moduleName'];
         $user = $this->authModules[$m]->Test_UID($externalReference);
         Log::d(var_export($user, true));
         if ($user !== false && $user->isActive()) {
            $this->Success($user, "keepalive");
         }
         else {
            $this->Fail("Bad credentials ($user)");
         }
      }
   }

   public function IsMember($groupName) {
      return in_array($groupName, $this->groups);
   }

   // Function to use to validate if a user is logged in or not.
   // Short name is intentional to not bloat code.
   public function ok() {
      //error_log("ok()");
      if ($this->loggedIn !== false) {
         return true;
      }
      else {
         return false;
      }
   }
   
   public function RequireGroup($groupName) {
      if (!in_array($groupName, $this->groups)) {
         header("HTTP/1.0 401 Unauthorized");
         header("Location: " . $this->groupError . "?GroupName=" . urlencode($groupName));
         exit();
      }
      return true;
   }
   
   public function StorePassword($password, $user = null) {
      // Store a password for the user.
      // Require: a password to set.
      // Optional: A user object to choose user.
      // Return: boolean
      return false;
   }
   
   // List all available groups
   public function ListGroups() {
      return array();
   }
   
   //public function SetGroups($user) {
   //   $m = $user->getAuthModule();
   //   $this->groups = $this->authModules[$m]->getGroups();
   //}
      
   protected function Fail($reason) {
      Log::d("Fail: $reason");
      $_SESSION[$this->loginSessionBase]['error'] = $reason;
      $this->errorReason = $reason;
   }

   protected function Success($user, $flag = null) {
      //error_log("Success");
      unset($_SESSION[$this->loginSessionBase]['errno']);
      $moduleName = $user->getAuthModule();
      $externalReference = $user->getExternalReference();
      $_SESSION[$this->loginSessionBase]['ip']  = $_SERVER['REMOTE_ADDR'];
      $_SESSION[$this->loginSessionBase]['moduleName'] = $moduleName;
      $_SESSION[$this->loginSessionBase]['externalReference'] = $externalReference;
      //error_log(var_export($_SESSION[$this->loginSessionBase], true));

      if ($flag == null) {
         $this->authModules[$moduleName]->UpdateLastLogin($user);
      }
      
      //if (isset($_POST[$this->loginFieldRemember]) || isset($_COOKIE[$this->loginFieldRemember])) {
      if (isset($_POST[$this->loginFieldRemember]) || isset($_SESSION[$this->loginSessionBase][$this->loginFieldRemember])) {
         //$params = session_get_cookie_params();
         $_SESSION[$this->loginSessionBase][$this->loginFieldRemember] = true;
         setcookie(
            session_name(),
            session_id(),
            time() + ini_get("session.gc_maxlifetime"),
            $this->loginCookiePath,
            $_SERVER['HTTP_HOST'], 
            ini_get("session.cookie_secure"),
            ini_get("session.cookie_httponly")
         );
      }
      
      // Retrieve a profile
      $this->profile = $this->profile->getByUser($user, true);
      $this->profileId = (int) $this->profile->getId();
      Log::d($this->profile);
      Log::d($this->profileId);

      // Set the login time in session.
      $_SESSION[$this->loginSessionBase]['timestamp'] = time();
      unset($_SESSION[$this->loginSessionBase]['error']);
      
      //$this->SetGroups($user);
      $this->loggedIn = $user;
      return $user;
   }
   
   public function Logout() {
      unset(
         $_SESSION[$this->loginSessionBase]['ip'],
         //$_SESSION[$this->loginSessionBase]['uid'],
         $_SESSION[$this->loginSessionBase]['externalReference'],
         $_SESSION[$this->loginSessionBase]['timestamp'],
         $_SESSION[$this->loginSessionBase]['error'],
         $_SESSION[$this->loginSessionBase]['errno'],
         $_SESSION[$this->loginSessionBase][$this->loginFieldRemember]
      );
      //setcookie(
      //   session_name(),
      //   "",
      //   time() - 3600 * 24 * 30,
      //   $this->loginCookiePath,
      //   $_SERVER['HTTP_HOST'],
      //   ini_get("session.cookie_secure"),
      //   ini_get("session.cookie_httponly")
      //);
      $this->loggedIn = false;
      $this->errorReason = null;
      $this->authProcessHasRun = false;
   }
      
   public function ChangeLanguage($language, $user = null) {
      $setLang = "";
      $langList = explode(",", $this->languageList);
      $language = trim($language);
      foreach($langList as $l) {
         if ($language == trim($l)) {
            if (is_null($user)) {
               $this->language = $language;
               $this->ChangeLanguageUpdate($this, $language);
            }
            else {
               $user->language = $language;
               $this->ChangeLanguageUpdate($user, $language);
            }
         }
      }
   }
   
   protected function ChangeLanguageUpdate($user, $language) {
      return;
   }
   
   public function setProfile($profile) {
      $this->profile = $profile;
   }
   
   protected function getDesiredModule() {
      $m = "zzzzUNSETzzzz";
      if (isset($_REQUEST[$this->loginFieldModule])) {
         $m = $_REQUEST[$this->loginFieldModule];
      }
      if (isset($this->authModules[$m])) {
         return $m;
      }
      else {
         return $this->defaultAuthModule;
      }
   }
}
?>
