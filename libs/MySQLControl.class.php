<?php
class MySQLControl {
   // Version timestamp: 2008-12-23 09:41:56

   //
   // MySQL Specific instructions.
   public $hostname = "localhost";
   public $username = "root";
   public $password = "";
   public $database = "test";
   public $port     = 3306;
   public $debug    = false;
   public $charset  = "utf8";

   //
   // MySQL Specific vars, used mostly for internal use.
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
   function __construct($datum = null) {
      $this->GPC = get_magic_quotes_gpc();
   }

   function __destruct() {
      @mysqli_close($this->session);
   }

   function __toString() {
      return $this->counter."";
   }

   function affected_rows($resource = null) {
      if (!is_null($resource)) {
         return mysqli_affected_rows($resource);
      }
      elseif (!is_null($this->session)) {
         return mysqli_affected_rows($this->session);
      }
      else {
         return false;
      }
   }


   /**
   // Connect to the almighty MySQL-database. Automaticly called when running
   // the site's first query.
   **/
   function connect() {
      if (!$this->session) {
         $this->session = mysqli_connect(
            $this->hostname,
            $this->username,
            $this->password,
            $this->database,
            $this->port
         );
         mysqli_query($this->session, "SET NAMES '".$this->charset."'");
      }
   }

   function insert_id($resource = null) {
      if (!is_null($resource)) {
         return mysqli_insert_id($resource);
      }
      elseif (!is_null($this->session)) {
         return mysqli_insert_id($this->session);
      }
      else {
         return false;
      }
   }

   function errno() {
      if (mysqli_connect_errno()) {
         return mysqli_connect_errno();
      }
      else {
         return mysqli_errno($this->session);
      }
   }

   function error() {
      if (mysqli_connect_errno()) {
         return mysqli_connect_error();
      }
      else {
         return mysqli_error($this->session);
      }
   }

   function fetch_assoc($resource = null) {
      if ($resource === false) {
         return false;
     }
     elseif (!is_null($resource)) {
         return mysqli_fetch_assoc($resource);
      }
      elseif (!is_null($this->lastQueryResource)) {
         return mysqli_fetch_assoc($this->lastQueryResource);
      }
      else {
         return false;
      }
   }

   function fetch_object($resource = null) {
      if ($resource === false) {
         return false;
     }
     elseif (!is_null($resource)) {
         return mysqli_fetch_object($resource);
      }
      elseif (!is_null($this->lastQueryResource)) {
         return mysqli_fetch_object($this->lastQueryResource);
      }
      else {
         return false;
      }
   }

   function fetch_row($resource = null) {
      if ($resource === false) {
         return false;
     }
     elseif (!is_null($resource)) {
         return mysqli_fetch_row($resource);
      }
      elseif (!is_null($this->lastQueryResource)) {
         return mysqli_fetch_row($this->lastQueryResource);
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
         return mysqli_num_rows($r);
      }
   }

   function result($resource = null, $index = 0) {
      if ($resource === false) {
         return false;
     }
     elseif (!is_null($resource)) {
         $x = mysqli_fetch_row($resource);
         return $x[$index];

      }
      elseif (!is_null($this->lastQueryResource)) {
         $x = mysqli_fetch_row($this->lastQueryResource);
         return $x[$index];
      }
      else {
         return false;
      }
   }

   /**
   // q() is the method for sending SQL into the MySQL server. It takes a
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
                  $sql .= mysqli_real_escape_string($this->session, stripslashes($peice));
               }
               else {
                  $sql .= mysqli_real_escape_string($this->session, $peice);
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
         $this->lastQueryResource = mysqli_query($this->session, $sql) or die(
            mysqli_errno($this->session)
            . ": " .
            mysqli_error($this->session)
         );
         return $this->lastQueryResource;
      }
      else {
         $this->lastQueryResource = mysqli_query($this->session, $sql);
         return $this->lastQueryResource;
      }
   }
   
   function toArray($resource = null,$oneFieldArray = false) {
      if (!is_null($resource)) { }
      elseif (!is_null($this->lastQueryResource)) { $resource = &$this->lastQueryResource; }
      else { return false; }

      $export = array();
      if ($oneFieldArray && mysqli_num_fields($resource) == 1) {
         while ($data = mysqli_fetch_row($resource)) { $export[] = $data[0]; }
      }
      else{
         while ($data = mysqli_fetch_assoc($resource)) { $export[] = $data; }
      }
      return $export;
   }
}
?>
