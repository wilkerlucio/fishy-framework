<?php

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
	
	public static function camelize($name) {
		$name = strtolower($name);
		$normalized = array();
		
		$upper = true;
		
		for ($i = 0; $i < strlen($name); $i++) {
			$normalized[] = $upper ? strtoupper($name[$i]) : $name[$i];
			if ($upper) $upper = false;
			if ($name[$i] == '_') $upper = true;
		}
		
		return implode('', $normalized);
	}
}
