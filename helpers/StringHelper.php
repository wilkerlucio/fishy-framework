<?php

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
}
