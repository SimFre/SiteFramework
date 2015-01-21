<?php
class Log {
   public static $enabled = false;

   // Short name to make it less intrusive in code.
   public static function d($data) {
      if (Log::$enabled) {
         if (is_object($data) || is_array($data)) {
            Log::d(print_r($data, true));
         }
         else {
            error_log("::" . $data);
         }
      }
   }
    
}
?>