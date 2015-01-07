<?php
class FileManager {
   public $db;
   public $admin;
   public $path;
   
   function getInfo($FileID, $getDeleted = false) {
      $FileID = (int) $FileID;
      //$this->db->debug = 1;
      $result = $this->db->q("
         SELECT
            FileID,
            fi.CatID,
            IF (fi.CatID=0, '', fc.Name) as Category,
            IF (fi.CatID=0, 'Default', fc.Folder) as Folder,
            Size,
            ROUND(Size / 1024 / 1024, 1) as MBSize,
            SHA1,
            AvailableFrom,
            AvailableTo,
            Title,
            Description,
            Filename,
            RealName,
            Access,
            MIME,
            Visible,
            Eraser,
            Erased,
            Uploader,
            Uploaded,
            StartCounter,
            FinishCounter,
            fi.Language,
            case when AvailableFrom <= NOW() and AvailableTo >= NOW() then 1 else 0 end as Available
         FROM files fi
         LEFT JOIN files_categories fc ON fi.CatID=fc.CatID
         WHERE FileID = ", $FileID," LIMIT 1
      ");

      if ($this->db->num_rows($result) == 1) {
         $o = $this->db->fetch_object($result);
         if ($o->Eraser != null && $getDeleted != true) {
            return false;
         }

         $o->URL = "http://" . $_SERVER['SERVER_NAME'] . "/files/" . $FileID . "/" . urlencode($o->Filename);
         $f = new UploadedFile($this->db, $this->admin, $this->path);
         foreach ($o as $key => $value) {
            $f->$key = $value;
         }
         $f->online = true;
         return $f;
      }
      else {
         return false;
      }
   }
   
   function getMenu($Language, $Category) {
      $results->CatID = 0;
      $results->Head = "";
      $results->Name = "";
      $results->Language = "";
      $results->Folder = "";
      $results->Values = array();
      $results->Length = 0;
      
      $catQry = $this->db->q("
         SELECT
            CatID, Name, Language, Folder
         FROM
            files_categories
         WHERE
            Name = '", $Category, "' and Language = '", $Language, "'
         LIMIT 1
      ");
      
      if ($this->db->num_rows() == 1) {
         $v = $this->db->fetch_object();
         $results->CatID = $v->CatID;
         $results->Head = "{|" . $v->Name . "|}";
         $results->Name = $v->Name;
         $results->Language = $v->Language;
         $results->Folder = $v->Folder;

         $fileQuery = $this->db->q("
            SELECT
               FileID
            FROM files fi
            WHERE
               CatID = ", $v->CatID, "
               AND Visible = 'Y'
               AND AvailableFrom < NOW()
               AND AvailableTo > NOW()
               ORDER BY Sorting DESC, FileID DESC, Title ASC, Filename ASC
         ");
         
         while ($row = $this->db->fetch_object($fileQuery)) {
            $results->Values[] = $this->getInfo($row->FileID);
            $results->Length++;
         }
      }

      return $results;
   }
   
   //function upload($Field = "File", $Category = "Misc", $Title = null, $Menu = null, $Language) {
   function upload($Language, $Field = "File") {
      if (!array_key_exists($Field, $_FILES)) {
         return false;
      }
      
      $targetDirectory = $this->path . '/' . $Language . '/Default';
      if (!is_dir($targetDirectory)) {
         if (!mkdir($targetDirectory, 0777, true)) {
            error_log("Could not create: " . $targetDirectory);
            return false;
         }
      }
      
      // Make sure the file gets a unique filename
      $originalFilename = $_FILES[$Field]['name'];
      $tempFilename = uniqid();
      $path = $targetDirectory . '/' . $tempFilename;
      $tempPath = $targetDirectory . '/' . uniqid();
      mkdir($tempPath);

      if (move_uploaded_file($_FILES[$Field]['tmp_name'], $tempPath . "/" . $originalFilename)) {
         if (function_exists("finfo_open")) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $MIME = finfo_file($finfo, $tempPath . "/"  . $originalFilename);
            finfo_close($finfo);
         }
         elseif (class_exists("MIME_Type")) {
            $MIME = strval(MIME_Type::autoDetect(strtolower($tempPath . "/" . $originalFilename)));
         }
         else {
            $MIME = "application/octet-stream";
         }
         rename($tempPath . "/"  . $originalFilename, $path);
         rmdir($tempPath);
         $hash = sha1_file($path);
         $size = $_FILES[$Field]['size'];
         
         //if ($MIME == "Sorry, couldn't determine file type.") {
         $sql = $this->db->q("
            INSERT INTO files
               (Filename, Language, Size, SHA1, RealName, Uploader, Uploaded, MIME)
            VALUES (
               '", $originalFilename, "',
               '", $Language, "',
               ", $size, ",
               '", $hash, "',
               '", $tempFilename, "',
               '", $this->admin->uid, "',
               NOW(),
               '", $MIME, "'
            )
         ");
         if ($this->db->errno() == 0) {
            $r = Array();
            $id = $this->db->insert_id();
            $r = $this->getInfo($id);
            return $r;
          }
          else {
            error_log("An error occured when inserting file to db.");
            error_log($this->db->errno() . ": " . $this->db->error());
            error_log($this->db->lastQueryText);
            return false;
         }
      }
      else {
         error_log("Could not move uploaded file. (" . $_FILES[$Field]['tmp_name'] . ") to (" . $tempPath . "/" . $originalFilename . ")");
         return false;
      }
   }
}
?>