<?php
error_reporting(E_ALL);
set_time_limit(300);
ini_set("session.gc_maxlifetime", 3600 * 24 * 7);
define("ROOT", $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR);
define("SF_PATH", $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "SiteFramework" . DIRECTORY_SEPARATOR);

require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "MySQLControl.class.php";
$db = new MySQLControl();
$db->hostname = "";
$db->username = "";
$db->password = "";
$db->database = "";
$db->debug    = false;

require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Site.class.php";
$site = new Site();
$site->StripPasswords = true;
$site->Domains = Array(
   '/localhost/'         => "en_US"
); 
$site->TemplatePath = ROOT . "templates";
$site->FilesPath    = ROOT . "files";
$site->Template     = "Basic.html";
$site->Languages    = "en_US";
$site->db           = &$db;

require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Admin.class.php";
require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Admin_User.class.php";
require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Admin_Auth.class.php";
require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Admin_Auth_adLDAP.class.php";
$admin = new Admin();
$site->admin    = &$admin;
$admin->addAuthModule(new Admin_Auth_adLDAP());
$admin->timeout = 3600 * 24 * 6;
$admin->login   = "login.php";
$admin->return  = "index.php";
$admin->groupError = "groupFail.php";
$admin->languageList = "en_US";
$admin->default_language = "en_US";
$admin->db      = &$db;
$admin->AutomaticAuth();

$admin->baseDN = "DC=example,DC=com";
$admin->dc_hostname = "";
$admin->dc_username = "";
$admin->dc_password = ""
$admin->dc_port = 3268; // null for default. 389 for LDAP, 636 for LDAPS, 3268 for GC-LDAP, 3269 for GC-LDAPS.
$admin->account_suffix = "@example.com";
$admin->account_create = true;
$admin->ad_group_map['en_US']['Attributes'] = "Attributes";
$admin->ad_group_map['en_US']['Users']      = "Admin";
$admin->ad_group_map['en_US']['Superuser']  = "Superuser";

require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "FileManager.class.php";
require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "UploadedFile.class.php";
$site->FileManager = new FileManager();
$site->FileManager->db    = &$site->db;
$site->FileManager->admin = &$site->admin;
$site->FileManager->path  = $site->FilesPath;
$FileManager = &$site->FileManager;

?>
