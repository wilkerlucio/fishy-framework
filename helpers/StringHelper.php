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
			$c = ord($string[$i]);
			
			if ($c == 45 || ($c > 47 && $c < 58) || ($c > 96 && $c < 123)) {
				$out .= $string[$i];
			}
		}
		
		return $out;
	}
	
	/**
	 * Truncate a string
	 * 
	 * @return string The truncated string
	 * @param string $string The string to be truncated
	 * @param integer $length The max length of string
	 * @param boolean $preserve_words[optional] Pass true if you want to preserve the words of string
	 * @param string $padding[optional] You can use this to change the default padding string
	 */
	public static function truncate($string, $length, $preserve_words = false, $padding = "...")
	{
		if (strlen($string) <= $length) return $string;
		
		$truncated = substr($string, 0, $length - strlen($padding));
		
		if ($preserve_words) {
			while ($string[strlen($truncated)] != ' ') {
				$truncated = substr($truncated, 0, strlen($truncated) - 1);
			}
		}
		
		$truncated .= $padding;
		
		return $truncated;
	}
	
	/**
	 * Parse a simple string template with given parameters
	 * 
	 * You should use # to define variables, example:
	 *   Fishy_StringHelper::simple_template("Hello #some, welcome!", array("some" => "World"));
	 * 
	 * This sample will output: Hello World, welcome!
	 * 
	 * The variables inside template should contains only alphabetic chars, any other char will
	 * stop the variable name parsing.
	 * 
	 * @return string
	 * @param string $template The string containing the template to be parsed
	 * @param array $vars The parameters to include into template
	 */
	public static function simple_template($template, $vars)
	{
		$output = "";
		$var_reg = "";
		$mode = 0;
		
		for ($i = 0; $i < strlen($template); $i++) {
			$char = $template[$i];
			
			if ($mode == 0) {
				switch ($char) {
					case '#':
						$mode = 1;
						$var_reg = "";
						break;
					default:
						$output .= $char;
				}
			} elseif ($mode == 1) {
				$code = ord($char);
				
				if ($code > 96 && $code < 123) {
					$var_reg .= $char;
					
					if ($i == (strlen($template) - 1)) {
						$output .= $vars[$var_reg];
					}
				} else {
					$output .= $vars[$var_reg];
					$output .= $char;
					$mode = 0;
				}
			}
		}
		
		return $output;
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
	
	public static function zero_fill($string, $n)
	{
		$string = $string . '';
		
		while(strlen($string) < $n) {
			$string = '0' . $string;
		}
		
		return $string;
	}
	
	public static function force_http($string)
	{
		if (!self::starts_with($string, 'http://')) {
			$string = 'http://' . $string;
		}
		
		return $string;
	}
}
