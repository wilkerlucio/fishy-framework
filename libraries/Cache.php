<?php

class Fishy_Cache
{
	public static function normalize_path($path)
	{
		return FISHY_CACHE_PATH . '/' . trim($path, '/');
	}
	
	public static function clear_cache($uri, $parse = true)
	{
		global $ROUTER;
		
		if ($parse) {
			$uri = $ROUTER->parse($uri);
		}
		
		$uri = self::normalize_path($uri);
		
		if (is_file($uri)) {
			unlink($uri);
		}
	}
	
	public static function cache($route, $data)
	{
		$file = self::normalize_path($route);
		
		Fishy_DirectoryHelper::mkdir($file, true);
		
		file_put_contents($file, $data);
	}
	
	public static function page_cache($route)
	{
		$path = self::normalize_path($route);
		
		if (is_file($path)) {
			$mime = mime_content_type($path);
			
			if ($mime) {
				header("Content-Type: $mime");
			}
			
			header("Content-Length: " . filesize($path));
			
			$file = fopen($path, 'rb');
			$chunksize = 1024 * 5;
			
			while(!feof($file) and (connection_status() == 0)) {
				$buffer = fread($file, $chunksize);
				echo $buffer;
				flush();
			}
			
			fclose($file);
			
			exit;
		}
	}
}
