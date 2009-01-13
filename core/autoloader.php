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

require_once FISHY_SYSTEM_HELPERS_PATH . '/StringHelper.php';

function fishy_autoloader($classname)
{
	//check if class already exists
	if (class_exists($classname)) {
		return true;
	}
	
	//check if is a core library
	if (Fishy_StringHelper::starts_with($classname, FISHY_SYSTEM_CLASS_PREFIX)) {
		$filename = substr($classname, strlen(FISHY_SYSTEM_CLASS_PREFIX));
		$path = FISHY_SYSTEM_LIBRARIES_PATH . '/' . $filename . '.php';
		
		if (file_exists($path)) {
			require_once $path;
			
			if (class_exists($classname)) {
				return true;
			}
		}
		
		$path = FISHY_SYSTEM_HELPERS_PATH . '/' . $filename . '.php';

		if (file_exists($path)) {
			require_once $path;

			if (class_exists($classname)) {
				return true;
			}
		}
	}
	
	//check if the class is a controller
	if (Fishy_StringHelper::ends_with($classname, 'Controller')) {
		$path = FISHY_CONTROLLERS_PATH;
		
		$bits = explode('_', $classname);
		
		while (count($bits) > 1) {
			$path .= '/' . strtolower(array_shift($bits));
		}
		
		$path .= '/' . substr(array_shift($bits), 0, -strlen('Controller')) . '.php';
		
		if (file_exists($path)) {
			require_once $path;
			
			if (class_exists($classname)) {
				return true;
			}
		}
	}
	
	//try to load user helper
	if (Fishy_StringHelper::ends_with($classname, 'Helper')) {
		$path = FISHY_HELPERS_PATH;
		
		$bits = explode('_', $classname);
		
		while (count($bits) > 1) {
			$path .= '/' . strtolower(array_shift($bits));
		}
		
		$path .= '/' . array_shift($bits) . '.php';
		
		if (file_exists($path)) {
			require_once $path;
			
			if (class_exists($classname)) {
				return true;
			}
		}
	}
	
	//try to load model
	$path = FISHY_MODELS_PATH . '/' . $classname . '.php';
	
	if (file_exists($path)) {
		require_once $path;
		
		if (class_exists($classname)) {
			return true;
		}
	}
	
	//try user library
	$path = FISHY_LIBRARIES_PATH . '/' . $classname . '.php';
	
	if (file_exists($path)) {
		require_once $path;
		
		if (class_exists($classname)) {
			return true;
		}
	}
	
	//sorry, i can't found the class :(
	return false;
}

//register autoloader
spl_autoload_register('fishy_autoloader');
