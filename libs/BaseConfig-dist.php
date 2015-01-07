<?php
error_reporting(E_ALL);
set_time_limit(300);

// Allow for a 1 week login
ini_set("session.gc_maxlifetime", 3600 * 24 * 7);

define("LANGUAGE_PATH", "C:/WWW/languages/");

require_once "MySQLControl.class.php";
$db = new MySQLControl;
$db->hostname = "";
$db->username = "";
$db->password = "";
$db->database = "";
$db->debug    = false;

require_once "Admin.class.php";
require_once "Admin_Auth_adLDAP.class.php";
$admin = new Admin_Auth_adLDAP;
$admin->timeout = 3600 * 2;
$admin->login   = "login.php";
$admin->return  = "index.php";
$admin->SetKey("secret");
$admin->groupError = "groupFail.php";

$admin->db      = &$db;
$admin->baseDN = "DC=ad,DC=example,DC=com";
$admin->dc_hostname = "dc.example.com";
$admin->dc_username = "aduser";
$admin->dc_password = "adpass";
$admin->dc_port = null; // null for default. 389 for LDAP, 636 for LDAPS, 3268 for GC-LDAP, 3269 for GC-LDAPS.
$admin->account_suffix = "@example.com";
$admin->account_create = true;

// try logging in directly
$admin->AutomaticAuth();

//require_once "MIME/Type.php";
require_once "Site.class.php";
$site = new Site;
$site->StripPasswords = true;
$site->Domains = Array(
   '/localhost/'         => "en_US"






); 
$site->TemplatePath = "C:/WWW/templates/";
$site->FilesPath    = "C:/WWW/htdocs/files/";
$site->Template     = "Admin.tpl";
$site->Languages    = "en_US";
$admin->languageList = "en_US";
$admin->default_language = "en_US";
$site->db           = &$db;
$site->admin        = &$admin;

require_once "FileManager.class.php";
require_once "UploadedFile.class.php";
$site->FileManager = new FileManager;
$site->FileManager->db    = &$site->db;
$site->FileManager->admin = &$site->admin;
$site->FileManager->path  = "C:/WWW/htdocs/files/";
$FileManager = &$site->FileManager;

$admin->ad_group_map['en_US']['Attributes'] = "SEVAS BLT Admin";
$admin->ad_group_map['en_US']['Users']      = "SEVAS BLT Admin";
$admin->ad_group_map['en_US']['Superuser']  = "SEVAS BLT Superuser";

?>
