<?php

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
