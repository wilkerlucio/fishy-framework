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
