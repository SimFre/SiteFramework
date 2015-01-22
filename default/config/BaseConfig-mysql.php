<?php
error_reporting(E_ALL);
set_time_limit(300);
ini_set("session.gc_maxlifetime", 3600 * 24 * 7);
define("ROOT", $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR);
define("SF_PATH", ROOT . DIRECTORY_SEPARATOR);

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
require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Admin_Auth_MySQL.class.php";
require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Admin_Profile.class.php";
require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Admin_Profile_MySQL.class.php";
$admin = new Admin();
$admin->addAuthModule(new Admin_Auth_MySQL($db));
$site->admin    = &$admin;
$admin->setProfile(new Admin_Profile_MySQL($db));
$admin->timeout = 3600 * 24 * 6;
$admin->login   = "login.php";
$admin->return  = "index.php";
$admin->groupError = "groupFail.php";
$admin->languageList = "en_US";
$admin->default_language = "en_US";
$admin->db      = &$db;
$admin->AutomaticAuth();

require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "FileManager.class.php";
require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "UploadedFile.class.php";
$site->FileManager = new FileManager();
$site->FileManager->db    = &$site->db;
$site->FileManager->admin = &$site->admin;
$site->FileManager->path  = $site->FilesPath;
$FileManager = &$site->FileManager;
?>