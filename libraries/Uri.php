<?php

/*
 * Copyright 2008 Wilker Lucio <wilkerlucio@gmail.com>
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License. 
 */

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
