<?php

// http://forums.devshed.com/ldap-programming-76/ldapsearch-for-ad-disabled-accounts-466619.html
// 1.1 The format of the LDAP Matching Rule has the following syntax: 
// attributename:ruleOID:=value
// attributename: is the LDAPDisplayName of the attribute, like "userAccountControl".
// ruleOID : is 1.2.840.113556.1.4.803 for the LDAP_MATCHING_RULE_BIT_AND rule, which is TRUE if all bits match the value,
// value : is the decimal value that represents the bits to match.
// By combining the above knowledge we can make the following filters;
// 
// 1st Filter: "(&(objectClass=User)(userAccountControl:1.2.840.113556.1.4.803:=2))"
// This filter will get all the users with disable account.
// 
// 2nd Filter: "(&(objectClass=User)(!userAccountControl:1.2.840.113556.1.4.803:=2))"
// This filter will get all the users with enable accounts.
error_reporting(E_ALL);
require_once "adLDAP.php";
class Admin_Auth_adLDAP extends Admin {
   public $baseDN      = "DC=exmple,DC=com";
   public $dc_hostname = "";
   public $dc_username = "";
   public $dc_password = "";
   public $dc_port = null;
   public $ad_group_map;
   //public $language_lookup;
   public $db; // Link to DB holding all web users.
   public $account_suffix = "@mydomain.local";
   public $account_create = true;
   private $connected = false;

   public function __construct() {
      parent::__construct();      
      $this->ad_group_map = Array();
      //$this->language_lookup = Array("EN" => "en_US");
   }

   //function connect() {
   //   if (!$this->connected) {
   //      $this->connected = new adLDAP(
   //         array(
   //            "account_suffix"     => $this->account_suffix,
   //            "base_dn"            => $this->baseDN,
   //            "domain_controllers" => array($this->dc_hostname)
   //         )
   //      );
   //   }
   //   return $this->connected;
   //}
   
   private function ad() {
      if ($this->connected === false) {
         $this->connected = new adLDAP(
            array(
               "account_suffix"     => $this->account_suffix,
               "base_dn"            => $this->baseDN,
               "domain_controllers" => array($this->dc_hostname),
               "ad_username"        => $this->dc_username,
               "ad_password"        => $this->dc_password,
               "ldap_port"          => $this->dc_port,
               "recursive_groups"   => false
            )
         );
      }
      return $this->connected;
   }
   
   public function ListGroups() {
      //Lists all available/configured groups.
      //ldap_group_map['lang']

      $g = array();
      if (isset($this->ad_group_map[$this->language])) {
         foreach($this->ad_group_map[$this->language] as $shortName => $dn) {
            $g[] = $shortName;
         }
      }
      return $g;
   }

   public function SetGroups($user) {
      $groupList = $this->ad()->user_groups($user->username);
      if (isset($this->ad_group_map[$this->language])) {
         foreach($this->ad_group_map[$this->language] as $internalGroupName => $adGroupName) {
            if (is_array($groupList) && in_array($adGroupName, $groupList)) {
               $this->groups[] = $internalGroupName;
            }
         }
      }
      return count($this->groups);
   }

   public function Test_UID($uid) {
      $ext = "ERROR";
      $userLang = $this->default_language;
      $q = $this->db->q("SELECT UserID, Language, ExternalReference FROM users WHERE UserID='", $uid, "' and Erased=0");
      //error_log("UID: " . var_export($uid, true) . ", NumRows: " . $this->db->num_rows());
      if ($this->db->num_rows()) {
         $dbData = $this->db->fetch_assoc();
         $ext = $dbData['ExternalReference'];
         $userLang  = $dbData['Language'];
      }
      else {
         return false;
      }

      $adInfo = $this->ad()->user_info($ext, array("*"), true);
      //error_log("ext: " . var_export($ext, true));
      //error_log("userLang: " . var_export($userLang, true));
      //error_log("adInfo: " . var_export($adInfo, true));
      //error_log("this->Ad: " . print_r($this->ad(), 1));
      if ($adInfo) {
         $u = new stdClass();
         $u->uid       = $uid;
         $u->firstname = $adInfo[0]['givenname'][0];
         $u->surname   = $adInfo[0]['sn'][0];
         $u->email     = (empty($adInfo[0]['mail'][0]) ? $adInfo[0]['userprincipalname'][0] : $adInfo[0]['mail'][0]);
         $u->username  = $adInfo[0]['userprincipalname'][0]; //userPrincipalName
         $u->language  = $userLang;
         $u->active    = ($adInfo[0]['useraccountcontrol'][0] & 2 ? false : true);
         return $u;
      }
      else {
         return false;
      }
   }
   
   public function Test_User($username, $password) {
      $user = false;
      if ($this->ad()->authenticate($username, $password)) {
         $adInfo = $this->ad()->user_info($username . $this->account_suffix, array("objectguid"));
         $guid = $this->ad()->decodeGuid($adInfo[0]['objectguid'][0]);
         $uid = $this->getUID($guid);
         $user = $this->Test_UID($uid);
      }
      return $user;
   }
   
   //function languageLookup($lang) {
   //   if (isset($this->language_lookup[$lang])) {
   //      return $this->language_lookup[$lang];
   //   }
   //   else {
   //      return reset($this->language_lookup);
   //   }
   //}
   
   public function AddUser($username) {
      $adInfo = $this->ad()->user_info($username . $this->account_suffix, array("*"));
      if (is_array($adInfo)) {
         $ext = $this->ad()->decodeGuid($adInfo[0]['objectguid'][0]);      
         $this->db->q("SELECT UserID FROM users WHERE ExternalReference='", $ext, "' and Type='ActiveDirectory' and Erased=0");
         if ($this->db->num_rows()) {
            $dbData = $this->db->fetch_assoc();
            return $dbData['UserID'];
         }
         
         else {
            $this->db->q("
               INSERT INTO users (Username, Mail, Firstname, Surname, Active, Language, Type, Created, ExternalReference) VALUES (
                  '", $adInfo[0]['userprincipalname'][0], "',
                  '", (empty($adInfo[0]['mail'][0]) ? $adInfo[0]['userprincipalname'][0] : $adInfo[0]['mail'][0]), "',
                  '", $adInfo[0]['givenname'][0], "',
                  '", $adInfo[0]['sn'][0], "',
                  '", ($adInfo[0]['useraccountcontrol'][0] & 2 ? "No" : "Yes"), "',
                  '", $this->default_language, "',
                  'ActiveDirectory',
                  NOW(),
                  '", $ext, "'
               )
            ");
            
            return $this->db->insert_id();
         }
      }
      else {
         return -1;
      }
   }
   
   public function DeleteUser($uid) {
      $uid = (int) $uid;
      $this->db->q("UPDATE users SET Erased = NOW() WHERE UserID = $uid");
      return true;
   }

   public function getUID($ext) {
      $q = $this->db->q("SELECT UserID, Language FROM users WHERE ExternalReference='", $ext, "' and Type='ActiveDirectory' and Erased=0");
      if ($this->db->num_rows()) {
         $dbData = $this->db->fetch_assoc();
         return $dbData['UserID'];
      }
      elseif ($this->account_create) {
         //$this->db->debug = 1;
         $this->db->q("
            INSERT INTO users (Language, Type, Created, ExternalReference) VALUES (
               '", $this->default_language, "',
               'ActiveDirectory',
               NOW(),
               '", $ext, "'
            )
         ");
         return $this->db->insert_id();
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
      $this->db->q("
         UPDATE users
         SET
            LastIP    = '", $_SERVER['REMOTE_ADDR'], "',
            Username  = '", $user->username, "',
            Firstname = '", $user->firstname, "',
            Surname   = '", $user->surname, "',
            Mail      = '", $user->email, "',
            LastLogin = NOW()
         WHERE
            UserID = '", $i, "'
         LIMIT 1
      ");
      if ($this->db->affected_rows() == 1) {
         return true;
      }
      else {
         return false;
      }
   }

   private function ChangeLanguageUpdate($user, $language) {
      $uid = $user->uid;
      $this->db->q("update users set Language='", $language, "' where UserID='", $uid, "'");
   }
}
?>
