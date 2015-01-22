<?php
class Site {
   public $Head;
   public $Body;
   public $BodyParam;
   public $Config;
   public $Content;
   protected $Debug;
   public $Domains;
   public $C;
   //public $FilesPath;
   public $SID;
   protected $PageID;
   public $RequestID;
   public $LoadStart;
   public $LoadStop;
   public $Template;
   public $TemplatePath;
   public $Language;
   protected $Languages;
   public $admin;
   public $db;
   public $FileManager;
   public $Plugin;
   public $RawHead;
   public $StripPasswords = false;
   private $logRequest = true;

   function __construct() {
      ob_start();
      $this->Config = Array();
      $this->Plugin = Array();
      //$this->db = &$db;
      $this->LoadStart = microtime(true);
      $this->RequestID = $this->uuid();
      $this->RawHead = file_get_contents("php://input");
      $this->C = &$this->Content;
      $this->C['BODY'] = &$this->Body;
      $this->C['HEAD'] = &$this->Head;
      $this->C['TITLE'] = "";
      $this->C['STYLE'] = "";
      $this->Domains = array('.*' => "en_US");
      $this->Languages = array("en_US" => "en_US");
      $this->Language = "en_US";

      if (!headers_sent()) {
         ini_set("session.use_cookies", 1);
         ini_set("session.use_only_cookies", 0);
         session_name('SID');
         session_start();
         $this->SID = session_id();
         if (isset($_SERVER['SERVER_NAME'])) {
            setcookie(session_name(), session_id(), time()+7*24*3600, '/', $_SERVER['SERVER_NAME']);
         }
         header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
         header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
      }
   }

   function __destruct() {
      if (is_null($this->PageID)) {
         $this->__set("PageID", "");
      }

      // Render the page
      if (!empty($this->Template)) {
         $C = $this->Content;
         $C['BODY'] = ob_get_contents(); // This might cause memory issues in the future.
         ob_clean();

         $admin = &$this->admin;
         $site = &$this;
         $db = &$this->db;
         $FileManager = &$this->FileManager;
         include $this->TemplatePath .'/'. $this->Template;
         $template = ob_get_contents();
         ob_end_clean();

         // This is how we can catch php-code if we want to later:
         // '/<\?php\s+(.*)\s+\?'.'>/mi'

         $template = $this->Render($template);
         // $template = preg_replace_callback(
         //    '/{\|([A-Za-z0-9_]+)\|}/',
         //    array($this, "Render_Replace"),
         //    $template
         // );
         echo $template;
         //echo "<!--\n" . print_r($C, true) . "\n-->\n";
      }
      else {
         ob_end_flush();
      }

      // Discard of any password data from POST variable.
      // Any POST variable containing PW or PASSWORD (case insensitive) will be
      // replaced with "********" so that the password will not be logged.
      $tempPOST = $_POST;
      if ($this->StripPasswords) {
         $keys = preg_grep('/(PW|PASSWORD)/i', array_keys($tempPOST), false);
         foreach($keys as $k) {
            if (is_string($tempPOST[$k])) {
               $tempPOST[$k] = "********";
            }
         }
      }

      $this->LoadStop = microtime(true);
      $LoadTime = $this->LoadStop - $this->LoadStart;
      $httpHost = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "");
      if ($this->setLogRequest()) {
         $this->db->q("
            INSERT INTO
               hits (
                  LoadStart, LoadStop, LoadTime, PageID, IP, Language, URL, RequestURI,
                  RawHead, ServerHost, SessionID, Method,
                  Cookie,        Session,        POST,        GET,        SERVER,
                  Cookie_Serial, Session_Serial, POST_Serial, GET_Serial, SERVER_Serial,
                  RequestID
               )
            VALUES (
               '", $this->LoadStart, "',
               '", $this->LoadStop, "',
               '", $LoadTime, "',
               '", $this->PageID, "',
               '", $_SERVER['REMOTE_ADDR'], "',
               '", $this->Session('Language'), "',
               '", $_SERVER['PHP_SELF'], "',
               '", $_SERVER['REQUEST_URI'], "',
               '", $this->RawHead, "',
               '", $httpHost, "',
               '", $this->SID, "',
               '", $_SERVER['REQUEST_METHOD'], "',
               '", print_r($_COOKIE, true), "',
               '", print_r($_SESSION, true), "',
               '", print_r($tempPOST, true), "',
               '", print_r($_GET, true), "',
               '", print_r($_SERVER, true), "',
               '", serialize($_COOKIE), "',
               '", serialize($_SESSION), "',
               '", serialize($tempPOST), "',
               '", serialize($_GET), "',
               '", serialize($_SERVER), "',
               '", $this->RequestID, "'
            )
         ");
      }
   }
   function __get($key) {
      switch ($key) {
         case "PageID":
            return $this->PageID;
         case "Languages":
            return $this->Languages;
      }
   }

   function __set($key, $value) {
      switch ($key) {
         case "Debug":
            if ($value) {
               ini_set("display_errors", "On");
            }
            else {
               ini_set("display_errors", "Off");
            }
         break;

         case "PageID":
            $this->PageID = $value;
            $this->db->q("
               SELECT AttributeName, AttributeValue, ContentType
               FROM attributes WHERE
               Language = '{$this->Language}' AND
               (PageID = '' OR PageID = '",$this->PageID,"')
               ORDER BY AttributeName ASC, PageID ASC
            ");

            while($arr = $this->db->fetch_assoc()) {
               $n = $arr['AttributeName'];
               $c = $arr['ContentType'];
               $v = $arr['AttributeValue'];
               $v = $this->ParseContent($v, $c);
               $this->Content[$n] = $v;
            }
         break;

         case "Languages":
            $this->setLanguage($value);
         break;
      }
   }

   function Attribute($Attribute) {
      return $this->Render_Replace(array(1 => $Attribute));
   }

   function getDefaultLanguage() {
      if (!isset($_SERVER['HTTP_HOST'])) {
         return reset($this->Languages);
      }
      elseif (count($this->Domains) > 0) {
         foreach($this->Domains as $pattern => $language) {
            if (preg_match($pattern, $_SERVER['HTTP_HOST'])) {
               //error_log("Setting lang: $language");
               return $language;
            }
         }
         //error_log("Found no matching language, falling back on default");
         return reset($this->Languages);
      }
      // elseif (preg_match('/\.(se|dk|no)$/i', $_SERVER['HTTP_HOST'], $language)) {
      //
      //    // This should use patterns found in $this->Domains instead. Fallback is first lang.
      //
      //    switch ($language[1]) {
      //       case 'se': return 'sv_SE';
      //       case 'dk': return 'da_DK';
      //       case 'no': return 'no_NO';
      //       default: return reset($this->Languages);
      //    }
      // }
      else {
         //error_log("There are no language definitions.");
         return reset($this->Languages);
      }
   }

   // Moved to FileManager.class.php
   //function FileUpload($Field = "File", $Category = "Misc", $Title = null, $Menu = null) {
   //   $targetDirectory = $this->FilesPath . '/' . $this->Language . '/' . $Category;
   //   if (!array_key_exists($Field, $_FILES)) {
   //      return false;
   //   }
   //
   //   if (!is_dir($targetDirectory)) {
   //      if (!mkdir($targetDirectory)) {
   //         error_log("Could not create: " . $targetDirectory);
   //         return false;
   //      }
   //   }
   //
   //   // Make sure the file gets a unique filename
   //   $filename = $_FILES[$Field]['name'];
   //
   //   $search  = "‚·‡Â‰ˆÙÛÚÈËÍ¯Ê¬¡¿≈ƒ÷‘”“…» ÿ∆";
   //   $replace = "aaaaaooooeeeoaAAAAAOOOOEEEOA";
   //   $s = array();
   //   $r = array();
   //   for($i = 0; $i < strlen($search); $i++) {
   //      $s[] = substr($search, $i, 1);
   //      $r[] = substr($replace, $i, 1);
   //   }
   //   $filename = str_replace($s, $r, $filename);
   //   $filename = preg_replace('/(\.\.|[^A-Za-z0-9\.\-_])/', "_", $filename);
   //
   //   $fx = array($filename);
   //   $loop = 0;
   //
   //   $originalFilename = $filename;
   //   $newfilename = $filename;
   //   // In case of filename-collition... set a number on the file.
   //   while(is_file($targetDirectory . '/' . $newfilename)) {
   //      if ($loop == 0) {
   //         $fx = explode(".", $filename);
   //         $fx[] = end($fx);
   //         $fx[key($fx)] = "%u";
   //         $filename = implode(".", $fx);
   //      }
   //      $newfilename = sprintf($filename, $loop);
   //      $loop++;
   //   }
   //   $filename = $newfilename;
   //   $path = $targetDirectory . '/' . $filename;
   //   if (move_uploaded_file($_FILES[$Field]['tmp_name'], $path)) {
   //      $hash = sha1_file($path);
   //      $size = $_FILES[$Field]['size'];
   //      $MIME = strval(MIME_Type::autoDetect(strtolower($path)));
   //      if ($MIME == "Sorry, couldn't determine file type.") {
   //         $MIME = "application/octet-stream";
   //      }
   //      $sql = $this->db->q("
   //         INSERT INTO files
   //            (Filename, Language, Size, SHA1, Category, Path, Title, Uploader, Uploaded, MimeType, AvailableInMenu)
   //         VALUES (
   //            '", $originalFilename, "',
   //            '", $this->Language, "',
   //            ", $size, ",
   //            '", $hash, "',
   //            '", $Category, "',
   //            '", $filename, "',
   //            '", $Title, "',
   //            '", $this->admin->uid, "',
   //            NOW(),
   //            '", $MIME, "',
   //            '", $Menu, "'
   //         )
   //      ");
   //      if ($this->db->errno() == 0) {
   //         $r = Array();
   //         $r['FileID'] = $this->db->insert_id();
   //         $r['URL']    = "http://" . $_SERVER['SERVER_NAME'] . "/files/" . $r['FileID'] . "/" . urlencode($originalFilename);
   //         $r['Filename'] = $originalFilename;
   //         $r['RealName'] = $filename;
   //         $r['SHA1'] = $hash;
   //         $r['MIME'] = $MIME;
   //         $r['Size'] = $size;
   //         $r['Path'] = $path;
   //         return $r;
   //       }
   //       else {
   //         error_log("An error occured when inserting file to db.");
   //         error_log($this->db->errno() . ": " . $this->db->error());
   //         error_log($this->db->lastQueryText);
   //         return false;
   //      }
   //   }
   //   else {
   //      error_log("Could not move uploaded file.");
   //      return false;
   //   }
   //}

   function Input($type = "text", $name = false, $value = null, $selectedValue = false, $extra = null) {
      $validTypes = array("submit","button","reset","image","textarea","radio","checkbox","select");
      if (!$name) { $name = md5(microtime(true)); }

      switch($type) {
         case 'button':
         case 'hidden':
         case 'reset':
         case 'submit':
         case 'text':
            $out = "<input type=\"$type\" name=\"$name\" value=\"$value\" $extra/>";
         break;

         case 'checkbox':
         case 'radio':
            if ($value == $selectedValue) { $checked = 'checked="checked" '; }
            else { $checked = ""; }
            $out = "<input type=\"$type\" name=\"$name\" value=\"$value\" $checked $extra/>";
         break;

         case 'image':
         break;

         case 'select':
            if (is_array($value)) {
               $options = "";
               foreach ($value as $key => $opt) {
                  if ($key == $selectedValue) { $selected = ' selected="selected"'; }
                  else { $selected = ""; }
                  $options .= " <option value=\"$key\"$selected>$opt</option>";
               }
            }
            else {
               $options = " <option value=\"$value\" selected=\"selected\">$value</option>";
            }
            $out = "<select name=\"$name\" size=\"1\" $extra>$options</select>";
         break;

         case 'textarea':
            $out = "<textarea name=\"$name\" $extra>$value</textarea>";
         break;

         default:
            $out = "";
         break;
      }
      return $out;
   }

   function Parse(&$InputData, $Reference = true, $ContentType = "text/plain") {
      $Input = null;
      if ($Reference) { $Input = &$InputData; }
      else { $Input = $InputData; }

      if (is_array($Input)) {
         $InputArray = &$Input;
         $ReturnType = "array";
      }
      else {
         $InputArray[0] = &$Input;
         $ReturnType = "single";
      }

      foreach ($InputArray as $key => $value) {
         if (is_array($value)) {
            $value = call_user_func(array($this, __FUNCTION__), $value, $Reference, $ContentType);
         }
         else {
            switch($ContentType) {
               case "js/escaped":
                  $value = addslashes($value);
                  $value = str_replace("\r\n", "\n", $value);
                  $value = str_replace("\r", "\n", $value);
                  $value = str_replace("\n", "\\n", $value);
               break;

               case "text/php":
                  $value = "return <<<EOT\n" . $value . "\nEOT;\n";
                  $value = eval($value);
               break;

               case "text/html":
                  // Don't do anything, asume it's properly formatted.
               break;

               case "text/nobr":
                  $value = htmlspecialchars($value);
                  $value = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $value);
               break;

               case "text/plain":
               default:
                  $value = htmlspecialchars($value);
                  $value = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $value);
                  $value = nl2br($value);
               break;
            }
         }
         $InputArray[$key] = $value;
      }

      if ($ReturnType == "single") {
         return $InputArray[0];
      }
      else {
         return $InputArray;
      }
   }

   function ParseContent($Input, $ContentType = "text/plain") {
      // We can pass $Reference = true below since it's working
      // with a copy anyways.
      return $this->Parse($Input, true, $ContentType);
   }

   function Render($Text) {
      $Text = preg_replace_callback(
         '/{\|([A-Za-z0-9_]+)\|}/',
         array($this, "Render_Replace"),
         $Text
      );
      return $Text;
   }

   function Render_Replace($match) {
      $match = $match[1];
      if (array_key_exists($match, $this->Content)) {
         return $this->Content[$match];
      }
      else {
         $this->db->q("
            SELECT AttributeName, AttributeValue, ContentType
            FROM attributes
            WHERE
               AttributeName = '", $match, "'
               and Language = '", $this->Language, "'
               and (PageID = '' OR PageID = '",$this->PageID,"')
            ORDER BY
               PageID ASC
            LIMIT 1
         ");
         if ($this->db->num_rows() == 1) {
            $entry = $this->db->fetch_assoc();
            $c = &$entry['ContentType'];
            $k = &$entry['AttributeName'];
            $v = &$entry['AttributeValue'];
            $v = $this->ParseContent($v, $c);
            $this->Content[$k] = $v;
            return $v;
         }
         else {
            $k = $match;
            $v = '{|' . $match . '|}';
            $this->Content[$k] = $v;
            return '{|' . $match . '|}';
         }
      }
   }

   function Action_Param($Type, $Params) {
      $base = Array();
      switch($Type) {
         case "REQUEST": $base = $_REQUEST; break;
         case "GET":     $base = $_GET;     break;
         case "POST":    $base = $_POST;    break;
         case "COOKIE":  $base = $_COOKIE;  break;
         case "SESSION": $base = $_SESSION; break;
         default: return false;
      }

      foreach($Params as $key => $value) {
         if (isset($base[$value])) {
            $base = $base[$value];
         }
         else {
            return false;
         }
      }
      return $base;
   }

   function Cookie() {
      $args = func_get_args();
      return $this->Action_Param(strtoupper(__FUNCTION__), $args);
   }
   function Get() {
      $args = func_get_args();
      return $this->Action_Param(strtoupper(__FUNCTION__), $args);
   }
   function Post() {
      $args = func_get_args();
      return $this->Action_Param(strtoupper(__FUNCTION__), $args);
   }
   function Request() {
      $args = func_get_args();
      return $this->Action_Param(strtoupper(__FUNCTION__), $args);
   }
   function Session() {
      $args = func_get_args();
      return $this->Action_Param(strtoupper(__FUNCTION__), $args);
   }

   function setConfig($Language = null) {
      //if (is_null($Language)) {
      //   $Language = $this->Language;
      //}
      //
      //$this->db->q("
      //   SELECT ParamName, ParamType, ParamValue
      //   FROM attributes
      //   WHERE
      //      Language = 'xx_XX' or
      //      Language = '", $Language, "'
      //   ORDER BY
      //      Language DESC,
      //      ParamName ASC
      //");
      //while ($c = $this->db->fetch_assoc()) {
      //   $this->Config[$c['ParamName']] = $c['ParamValue'];
      //}
   }

   function setLogRequest($log = null) {
      if (is_null($log)) {
         return $this->logRequest;
      } else {
         $this->logRequest = (bool) $log;
         return $this->logRequest;
      }
   }

   function setLanguage($languages) {
      // $_SERVER["HTTP_ACCEPT_LANGUAGE"]
      // da == Danish
      // sv == Swedish
      // en-us == English/American
      // en == English/British
      // no == Norwegian

      $this->Languages = explode(",", $languages);
      $this->Languages = array_combine($this->Languages, $this->Languages);
      if ($this->Session('Language') == "") {
         $_SESSION['Language'] = $this->getDefaultLanguage();
      }

      if (($this->Get("Language") != $this->Session("Language")) && ($this->Get("Language") != "")) {
         $l = $this->Get("Language");
         if (array_key_exists($l, $this->Languages)) {
            $_SESSION['Language'] = $l;
         }
      }
      $this->Language = $this->Session('Language');
      $this->setConfig();
   }

   function uuid() {
      return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
         mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
         mt_rand( 0, 0x0fff ) | 0x4000,
         mt_rand( 0, 0x3fff ) | 0x8000,
         mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
      );
   }
}
?>
