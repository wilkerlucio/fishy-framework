<?php

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
