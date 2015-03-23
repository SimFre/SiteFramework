<?php
class SQLiteControl {
   // Version timestamp: 2015-03-23

   //
   // SQLite Specific instructions.
   public $database = "test";
   public $debug    = false;
   public $charset  = "utf8";

   //
   // SQLite Specific vars, used mostly for internal use.
   public $session  = false;

   //
   // Number of executed SQL queries
   public $counter = 0;

   //
   // Magic quotes on/off. Will be set in __construct()
   public $GPC;

   public $lastQueryResource = null;
   public $lastQueryText = "";

//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////

   /**
   // Construct sets the GPC-variable which will be used by q() for escaping
   // queries properly.
   **/
   function __construct() {
      $this->GPC = get_magic_quotes_gpc();
   }

   function __destruct() {
      @this->session->close());
   }

   function __toString() {
      return $this->counter."";
   }

   function affected_rows($resource = null) {
      if (!is_null($resource)) {
         return $resource->changes();
      }
      elseif (!is_null($this->session)) {
         return $this->session->changes();
      }
      else {
         return false;
      }
   }


   /**
   // Connect to the almighty SQLite3-database. Automaticly called when running
   // the site's first query.
   **/
   function connect() {
      if (!$this->session) {
         $this->session = new SQLite3(
            $this->database
         );
      }
   }

   function insert_id($resource = null) {
      if (!is_null($resource)) {
         return $resource->lastInsertRowID();
      }
      elseif (!is_null($this->session)) {
         return $this->session->lastInsertRowID();
      }
      else {
         return false;
      }
   }

   function errno() {
      return $this->session->lastErrorCode();
   }

   function error() {
      return $this->session->lastErrorMsg();
   }

   function fetch_assoc($resource = null) {
      if ($resource === false) {
         return false;
     }
     elseif (!is_null($resource)) {
         return $resource->fetchArray(SQLITE3_ASSOC);
      }
      elseif (!is_null($this->lastQueryResource)) {
         return $this->lastQueryResource->fetchArray(SQLITE3_ASSOC);
      }
      else {
         return false;
      }
   }

   function fetch_object($resource = null) {
      

      $data = $this->fetch_assoc($resource);
      if ($data === false) {
         return false;
      }
      else {
         $o = new stdClass();
         foreach ($data as $key => $value) {
            $o->$key = $value;
         }
         return $o;
      }
   }

   function fetch_row($resource = null) {
      if ($resource === false) {
         return false;
     }
     elseif (!is_null($resource)) {
         return $resource->fetchArray(SQLITE3_NUM);
      }
      elseif (!is_null($this->lastQueryResource)) {
         return $this->lastQueryResource->fetchArray(SQLITE3_NUM);
      }
      else {
         return false;
      }
   }

   function num_rows($resource = null) {
      $r = null;
      if (is_null($this->lastQueryResource)) { return false; }
      if (is_null($resource)) {
         $r = &$this->lastQueryResource;
      }
      else {
         $r = &$resource;
      }

      if (is_bool($r)) {
         return 0;
      }
      else {
         $rows = 0;
         $r->reset();
         while ($result->fetchArray()) {
            $rows += 1;
         }
         $r->reset();
         return $rows;
      }
   }

   /**
   // q() is the method for sending SQL into the SQLite session. It takes a
   // minumum of one argument, that being a query. The idea with this spcial
   // function is that it will automaticly escape data so that no dangerous
   // content can be thrown to the server. Effectivly remove SQL-injection
   // so to speak.
   **/
   function q() {
      if (!$this->session) { $this->connect(); }
      $args = func_get_args();
      if (count($args) < 1) { return false; }
      elseif (count($args) == 1) { $sql = $args[0]; }
      else {
         $sql = "";
         foreach($args as $key => $peice) {
            if ($key % 2 == 0) {
               // This will take every second argument as safe.
               $sql .= $peice;

            }
            else {
               if ($this->GPC) {
                  $sql .= $this->session->escapeString(stripslashes($peice));
               }
               else {
                  $sql .= $this->session->escapeString($peice);
               }
            }
               
         }
      }

      if (substr($sql, -1) != ";") {
         $sql .= ";";
      }
      $this->lastQueryText = $sql;
      $this->counter++;
      if ($this->debug) {
         print_r($sql);
         echo "\n";
         $this->lastQueryResource = $this->session->query($sql) or die(
            $this->errno()
            . ": " .
            $this->error()
         );
         return $this->lastQueryResource;
      }
      else {
         $this->lastQueryResource = $this->session->query($sql);
         return $this->lastQueryResource;
      }
   }

   function toArray($resource = null,$oneFieldArray = false) {
      if (!is_null($resource)) { }
      elseif (!is_null($this->lastQueryResource)) { $resource = &$this->lastQueryResource; }
      else { return false; }

      $export = array();
      if ($oneFieldArray && $resource->numColumns()) == 1) {
         while ($data = $resource->fetchArray(SQLITE3_NUM)) { $export[] = $data[0]; }
      }
      else{
         while ($data = $resource->fetchArray(SQLITE3_ASSOC) { $export[] = $data; }
      }
      return $export;
   }
}
?>
