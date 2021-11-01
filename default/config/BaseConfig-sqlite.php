<?php
error_reporting(E_ALL);
set_time_limit(300);
ini_set("session.gc_maxlifetime", 3600 * 24 * 7);
define("ROOT", $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR);
define("SF_PATH", ROOT);

require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Log.class.php";
Log::$enabled = false;

require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "SQLiteControl.class.php";
$db = new SQLiteControl();
$db->database = ROOT . "SiteFramework.sqlite";
$db->debug    = false;

require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Site.class.php";
$site = new Site();
$site->StripPasswords = true;
$site->Domains = Array(
   '/localhost/'         => "en_US"
);
$site->TemplatePath = SF_PATH . "templates";
$site->FilesPath    = SF_PATH . "files";
$site->Template     = "Basic.php";
$site->Languages    = "en_US";
$site->db           = &$db;

require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Admin.class.php";
require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Admin_User.class.php";
require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Admin_Auth.class.php";
require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Admin_Auth_SQLite.class.php";
require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Admin_Profile.class.php";
require_once SF_PATH. "libs" . DIRECTORY_SEPARATOR . "Admin_Profile_SQLite.class.php";
$admin = new Admin();
$site->admin    = &$admin;
$adminAuthSession = new Admin_Auth_SQLite($db);
$admin->addAuthModule($adminAuthSession);
$adminProfileSession = new Admin_Profile_SQLite($db);
$admin->setProfile($adminProfileSession);
$admin->timeout = 3600 * 24 * 6;
$admin->login   = "/login.php";
$admin->return  = "/login.php";
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