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
		
		return false;
	}
	
	public static function array_push(&$origin, $data)
	{
		foreach ($data as $value) {
			$origin[] = $value;
		}
	}
}
