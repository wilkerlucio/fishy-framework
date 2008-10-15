<?php

class Fishy_DirectoryHelper
{
	public static function mkdir($path, $remove_last = false)
	{
		$bits = preg_split('/(\/|\\\)/', $path);
		$current = '';
		
		if ($remove_last) {
			array_pop($bits);
		}
		
		foreach ($bits as $dir) {
			$current .= $dir . '/';
			
			if (!is_dir($current)) {
				mkdir($current);
			}
		}
	}
}
