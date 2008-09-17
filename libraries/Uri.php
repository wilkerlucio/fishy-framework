<?php

class Fishy_Uri {
    public static function get_querystring($include_get = false) {
        $script = $_SERVER['SCRIPT_NAME'];
        $request = $_SERVER['PHP_SELF'];
        
        $querystring = substr($request, strlen($script));
        
        //$querystring = $_SERVER['PATH_INFO'];
        
        return trim($querystring, '/');
    }
}
