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

class Fishy_ArrayHelper
{
	public static function binary_search($array, $value)
	{
		$x = 0;
		$y = count($array) - 1;
		
		while ($x <= $y) {
			$pivot = floor(($y - $x) / 2) + $x;
			$current = $array[$pivot];
			
			if ($current == $value) {
				return $pivot;
			}
			
			if ($current > $value) {
				$y = $pivot - 1;
			} else {
				$x = $pivot + 1;
			}
		}
		
		return -1;
	}
	
	public static function array_push(&$origin, $data)
	{
		foreach ($data as $value) {
			$origin[] = $value;
		}
	}
	
	public static function array_split($array, $parts)
	{
		$count = count($array);
		
		$pieces = floor($count / $parts);
		$rest = $count % $parts;
		
		$output = array();
		$x = 0;
		
		for ($i = 0; $i < $parts; $i++) {
			$segment = array();
			
			for ($z = 0; $z < $pieces; $z++) {
				$segment[] = $array[$x];
				
				$x++;
			}
			
			if ($rest > 0) {
				$segment[] = $array[$x];
				
				$rest--;
				$x++;
			}
			
			$output[] = $segment;
		}
		
		return $output;
	}
	
	/**
	 * Find the index of element into array
	 *
	 * @param array $haystack The array to find element
	 * @param mixed $needle The element to be found
	 * @return integer Return the index of element or -1 if the element isn't present
	 */
	public static function index_of($haystack, $needle)
	{
		foreach ($haystack as $key => $value) {
			if ($needle == $value) {
				return $key;
			}
		}
		
		return -1;
	}
	
	/**
	 * Group array into subgroups of data
	 *
	 * @param array $data The array containg current data
	 * @param integer $n The number of items per group
	 * @return array The data grouped
	 */
	public static function in_groups_of($data, $n)
	{
		$groups = array();
		$buffer = array();
		
		foreach ($data as $item) {
			$buffer[] = $item;
			
			if (count($buffer) == $n) {
				$groups[] = $buffer;
				$buffer = array();
			}
		}
		
		if (count($buffer) > 0) {
			$groups[] = $buffer;
		}
		
		return $groups;
	}
}
