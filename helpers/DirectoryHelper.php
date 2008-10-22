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
