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

/**
 * This class provides some string util functions for strings and texts
 *
 * @author Wilker Lucio
 */
class Fishy_StringHelper
{
	/**
	 * Check if a string starts with another string
	 *
	 * @param $haystack The string to search in
	 * @param $needle The string to be searched
	 * @return boolean True if found, false otherwise
	 */
	public static function starts_with($haystack, $needle)
	{
		$end_len = strlen($needle);
		
		if (strlen($haystack) < $end_len) {
			return false;
		}
		
		$bit = substr($haystack, 0, $end_len);
		
		return $bit == $needle;
	}
	
	/**
	 * Check if a string ends with another string
	 *
	 * @param $haystack The string to search in
	 * @param $needle The string to be searched
	 * @return boolean True if found, false otherwise
	 */
	public static function ends_with($haystack, $needle)
	{
		$end_len = strlen($needle);
		
		if (strlen($haystack) < $end_len) {
			return false;
		}
		
		$bit = substr($haystack, -$end_len);
		
		return $bit == $needle;
	}
	
	public static function camelize($name)
	{
		$name = strtolower($name);
		$normalized = "";
		
		$upper = true;
		
		for ($i = 0; $i < strlen($name); $i++) {
			$normalized .= $upper ? strtoupper($name[$i]) : $name[$i];
			if ($upper) $upper = false;
			if ($name[$i] == '_') $upper = true;
		}
		
		return $normalized;
	}
	
	/**
	 * Gives a normalized string that can be used at urls
	 *
	 * @param string $string The string to be converted
	 * @return string Converted string
	 */
	public static function normalize($string)
	{
		//first, set all to lowercase
		$string = strtolower($string);
		
		//convert spaces into dashes
		$string = str_replace(' ', '-', $string);
		
		//remove out of range characters
		$out = '';
		
		for ($i = 0; $i < strlen($string); $i++) { 
			if (ord($string[$i]) > 127) {
				continue;
			}
			
			$out .= $string[$i];
		}
		
		return $out;
	}
	
	/**
	 * Generates a random string
	 *
	 * @param integer $length The length of generated string
	 * @param string $charset The charset to be used
	 * @return string The string generated
	 */
	public static function random($length, $charset = 'abcdefghijklmnopqrstuvxywzABCDEFGHIJKLMNOPQRSTUVXYWZ0123456789')
	{
		$out = "";
		
		for ($i = 0; $i < $length; $i++) { 
			$index = rand(0, strlen($charset) - 1);
			
			$out .= $charset[$index];
		}
		
		return $out;
	}
}
