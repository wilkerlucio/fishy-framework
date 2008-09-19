<?php

class Fishy_Uri {
    public static function get_querystring($include_get = false) {
        $script = $_SERVER['SCRIPT_NAME'];
        $request = $_SERVER['PHP_SELF'];
        
        $querystring = substr($request, strlen($script));
        
        //$querystring = $_SERVER['PATH_INFO'];
        
        return trim($querystring, '/');
    }
    
    public static function segment($n) {
    	$string = self::get_querystring();
    	$bits = explode('/', trim($string, '/'));
    	
    	return isset($bits[$n]) ? $bits[$n] : null;
    }
}
