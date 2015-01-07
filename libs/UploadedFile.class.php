<?php
class UploadedFile {

   public $db;
   public $admin;
   public $basepath;
   public $online = false;
   
   protected $FileID;
   protected $CatID;
   protected $Size;
   public $MBSize;
   protected $SHA1;
   protected $AvailableFrom;
   protected $AvailableTo;
   protected $Title;
   protected $Description;
   protected $Filename;
   protected $RealName;
   protected $Access;
   protected $MIME;
   protected $Visible;
   protected $Eraser;
   protected $Erased;
   protected $Uploader;
   protected $Uploaded;
   protected $StartCounter;
   protected $FinishCounter;
   protected $Language;
   protected $Folder;
   protected $Category;
   protected $Available;
   public $URL;

   function __construct(&$db, &$admin, $path) {
      $this->db = &$db;
      $this->admin = &$admin;
      $this->basepath = $path;
   }
   
   function __get($key) {
      if (property_exists($this, $key)) {
         return $this->$key;
      }
      else {
         return null;
      }
   }
   
   function __set($key, $value) {
      if (property_exists($this, $key)) {
         if ($value != $this->$key) {
            if ($this->online) {
               switch($key) {
                  case "CatID":
                  case "Category":
                  return $this->move($value, $this->Filename);
                  break;
                  
                  case "Filename":
                  return $this->move($this->CatID, $value);
                  break;
                  
                  case "Language":
                  case "SHA1":
                  case "FileID":
                  case "MIME":
                  case "Eraser":
                  case "Erased":
                  case "Uploader":
                  case "Folder":
                  return false;

                  default:
                  //error_log("Setting Key: " . $key . ", Value: " . $value);
                  $this->$key = $value;
                  //error_log(var_export($this->db, true));
                  $this->db->q("UPDATE files SET $key = '", $value, "' WHERE FileID = ", $this->FileID, " LIMIT 1");
                  break;
               }
            }
            else {
               $this->$key = $value;
            }
         }
         return $value;
      }
      else {
         return false;
      }
   }
   
   function delete() {
      $this->AvailableTo = date("Y-m-d H:i:s");
      $this->Eraser = $this->admin->uid;
      $this->db->q("
         UPDATE files SET
            Eraser = '", $this->admin->uid, "',
            Erased = NOW(),
            AvailableTo = NOW()
         WHERE
            FileID = '", $this->FileID, "'
         LIMIT 1
      ");
   }
   
   function purge() {
      //$this->db->q("DELETE FROM files WHERE FileID = '", $this->FileID, "' LIMIT 1");
      $p = $this->basepath . "/" . $this->Language . "/" . $this->Folder;
      unlink($p . "/" . $this->RealName);
      $d = scandir($p);
      if (count($d) <= 2) {
         rmdir($p);
      }
   }
   
   private function move($Category, $Filename) {
      //error_log("Starting");
      $Folder = "";
      if (intval($Category) == 0) {
         $this->db->q("SELECT CatID, Folder FROM files_categories WHERE Name = '", $Category, "' AND Language = '", $this->Language, "' LIMIT 1");
         if ($this->db->num_rows() == 1) {
            $c = $this->db->fetch_object();
            $Category = (int) $c->CatID;
            $Folder = $c->Folder;
            //error_log("CI: " . $c->CatID . ", F: " . $c->Folder);
         }
         else {
            //error_log("no such cat name --" . $Category);
            return false;
         }
      }
      else {
         $Category = (int) $Category;
         $this->db->q("SELECT Folder FROM files_categories WHERE CatID = ", $Category, " AND Language = '", $this->Language, "' LIMIT 1");
         if ($this->db->num_rows() == 1) {
            $c = $this->db->fetch_object();
            $Folder = $c->Folder;
            //error_log("CI: " . $Category . ", F: " . $c->Folder);
         }
         else {
            //error_log("no such cat id --" . $Category);
            return false;
         }
      }
      
      //error_log("GOGO");

      $source = $this->basepath . '/'. $this->Language . '/' . $this->Folder . '/' . $this->RealName;
      $targetDirectory = $this->basepath . '/' . $this->Language . '/' . $Folder;

      if (!is_dir($targetDirectory)) {
         if (!mkdir($targetDirectory)) {
            error_log("Could not create: " . $targetDirectory);
            return false;
         }
      }
      
      // Make sure the file gets a unique filename
      $search  = "âáàåäöôóòéèêøüûÜÛÂÁÀÅÄÖÔÓÒÉÈÊØç";
      $replace = "aaaaaooooeeeouuUUAAAAAOOOOEEEOc";
      $s = array();
      $r = array();
      for($i = 0; $i < strlen($search); $i++) {
         $s[] = substr($search, $i, 1);
         $r[] = substr($replace, $i, 1);
      }
      $s[] = "æ"; $r[] = "ae";
      $s[] = "Æ"; $r[] = "AE";
      $s[] = "Œ"; $r[] = "OE";
      $s[] = "œ"; $r[] = "oe";
      $s[] = "ß"; $r[] = "ss";
      

      $Filename = str_replace($s, $r, $Filename);
      $Filename = preg_replace('/(\.\.|[^A-Za-z0-9\.\-_])/', "_", $Filename);
      
      $fx = array($Filename);
      $loop = 0;
      
      $originalFilename = $Filename;
      $newfilename = $Filename;
      // In case of filename-collition... set a number on the file.
      while(is_file($targetDirectory . '/' . $newfilename)) {
         if ($loop == 0) {
            $fx = explode(".", $Filename);
            $fx[] = end($fx);
            $fx[key($fx)] = "%u";
            $Filename = implode(".", $fx);
         }
         $newfilename = sprintf($Filename, $loop);
         $loop++;
      }

      $Filename = $newfilename;
      if (rename($source, $targetDirectory . '/' . $Filename)) {
         $p = $this->basepath . "/" . $this->Language . "/" . $this->Folder;
         $d = scandir($p);
         if (count($d) <= 2) {
            rmdir($p);
         }

         $this->RealName = $newfilename;
         $this->Filename = $originalFilename;
         $this->CatID    = $Category;
         $this->Folder   = $Folder;
         $this->db->q("
            UPDATE files SET
               RealName = '", $newfilename, "',
               Filename = '", $originalFilename, "',
               CatID    = '", $Category, "'
            WHERE
               FileID = ", $this->FileID, "
            LIMIT 1
         ");
         
         return true;
      }
      else {
         return false;
      }
   }
}
?>