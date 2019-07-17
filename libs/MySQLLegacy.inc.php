<?php
// To use this, make an instance of MySQLControl with the name $MYSQL_LEGACY_CONNECTION
// then just include this file and your old MySQL code should mostly work.


if (isset($MYSQL_LEGACY_CONNECTION)) {
    if (!function_exists("mysql_affected_rows")) {
        function mysql_affected_rows ($result) {
            global $MYSQL_LEGACY_CONNECTION;
            return $MYSQL_LEGACY_CONNECTION->affected_rows($result);
        }
    }
    if (!function_exists("mysql_close")) {
        function mysql_close () {
            global $MYSQL_LEGACY_CONNECTION;
            return $MYSQL_LEGACY_CONNECTION->close();
        }
    }
    if (!function_exists("mysql_connect")) {
        function mysql_connect ($server, $username, $password) {
            $c = new MySQLControl();
            $c->hostname = $server;
            $c->username = $username;
            $c->password = $password;
            return $c;
        }
    }
    if (!function_exists("mysql_error")) {
        function mysql_error ($link = null) {
            global $MYSQL_LEGACY_CONNECTION;
            return $MYSQL_LEGACY_CONNECTION->error();
        }
    }
    if (!function_exists("mysql_fetch_array")) {
        function mysql_fetch_array ($result) {
            global $MYSQL_LEGACY_CONNECTION;
            return $MYSQL_LEGACY_CONNECTION->fetch_array($result);
        }
    }
    if (!function_exists("mysql_fetch_assoc")) {
        function mysql_fetch_assoc ($result) {
            global $MYSQL_LEGACY_CONNECTION;
            return $MYSQL_LEGACY_CONNECTION->fetch_assoc($result);
        }
    }
    if (!function_exists("mysql_fetch_object")) {
        function mysql_fetch_object ($result) {
            global $MYSQL_LEGACY_CONNECTION;
            return $MYSQL_LEGACY_CONNECTION->fetch_object($result);
        }
    }
    if (!function_exists("mysql_free_result")) {
        function mysql_free_result () {
            global $MYSQL_LEGACY_CONNECTION;
            return $MYSQL_LEGACY_CONNECTION->free_result();
        }
    }
    if (!function_exists("mysql_insert_id")) {
        function mysql_insert_id () {
            global $MYSQL_LEGACY_CONNECTION;
            return $MYSQL_LEGACY_CONNECTION->insert_id();
        }
    }
    if (!function_exists("mysql_num_rows")) {
        function mysql_num_rows ($result) {
            global $MYSQL_LEGACY_CONNECTION;
            return $MYSQL_LEGACY_CONNECTION->num_rows($result);
        }
    }
    if (!function_exists("mysql_query")) {
        function mysql_query ($query) {
            global $MYSQL_LEGACY_CONNECTION;
            return $MYSQL_LEGACY_CONNECTION->q($query);
        }
    }
    //Not sure if this should be replicated... probably unsafe.
    //if (!function_exists("mysql_real_escape_string")) {
    //    function mysql_real_escape_string ($query) {
    //        global $MYSQL_LEGACY_CONNECTION;
    //        return $MYSQL_LEGACY_CONNECTION->q($query);
    //    }
    //}
    if (!function_exists("mysql_result")) {
        function mysql_result ($result) {
            global $MYSQL_LEGACY_CONNECTION;
            return $MYSQL_LEGACY_CONNECTION->result($result);
        }
    }
    
    if (!function_exists("mysql_select_db")) {
        function mysql_select_db ($db) {
            global $MYSQL_LEGACY_CONNECTION;
            return $MYSQL_LEGACY_CONNECTION->database = $db;
        }
    }
}
?>