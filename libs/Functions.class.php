<?php

class Functions {
   // $insertstring - the string you want to insert
   // $intostring - the string you want to insert it into
   // $offset - the offset
   static function str_insert($insertstring, $intostring, $offset) {
     $part1 = substr($intostring, 0, $offset);
     $part2 = substr($intostring, $offset);
     return $part1 . $insertstring . $part2;
   }  
   static function multidimensionalArrayMap($func, $arr) {
      $newArr = array();
      foreach($arr as $key => $value) {
         $newArr[$key] = (is_array($value) ? $this->multidimensionalArrayMap($func,$value) : $func($value));
      }
      return $newArr;
   }
   static function entityArray($input) {
      return $this->multidimensionalArrayMap("htmlentities",$input);
   }

   static function hasData($input = null) {
      $input = html_entity_decode($input);
      $input = rawurldecode($input);
      $input = trim($input);
      return (empty($input) ? 0 : 1);
   }

   static function uuid() {
      return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
         mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
         mt_rand( 0, 0x0fff ) | 0x4000,
         mt_rand( 0, 0x3fff ) | 0x8000,
         mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
      );
   }
}
?>