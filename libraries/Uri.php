<?php

class Fishy_Uri {
	public static function get_querystring($include_get = false) {
		$script = $_SERVER['SCRIPT_NAME'];
		$request = $_SERVER['PHP_SELF'];
		
		$querystring = substr($request, strlen($script));
		
		//$querystring = $_SERVER['PATH_INFO'];
		
		return trim($querystring, '/');
	}
	
	public static function segment_array()
	{
		$string = self::get_querystring();
		return explode('/', trim($string, '/'));
	}
	
	public static function segment($n) {
		$bits = self::segment_array();
		
		return isset($bits[$n]) ? $bits[$n] : null;
	}
	
	public static function segment_slice($offset = 0, $length = null)
	{
		$bits = self::segment_array();
		
		return implode('/', array_slice($bits, $offset, $length));
	}
}
