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
 * Simple error logger
 *
 * @param string $file file name
 * @param string $error error message
 */
function log_error($file, $error)
{
	$path = FISHY_LOG_PATH . "/" . date("Y_m_d") . "_{$file}";
	$message = date("H:i:s") . " - " . $_SERVER["REMOTE_ADDR"] . "\n" . $error . "\n" . str_repeat("=", 80) . "\n\n";
	
	$file = fopen($path, "ab");
	fwrite($file, $message);
	fclose($file);
}

/**
 * Check if is running on production environment
 *
 * @return boolean
 */
function production_environment()
{
	return file_exists(FISHY_ROOT_PATH . "/.production");
}

/**
 * Dispatch an error page
 *
 * @param Exception the throw exception
 * @param integer $error_type error type to display
 */
function dispatch_error($exception, $error_type = 501)
{
	if (!production_environment()) throw $exception;
	
	$error_output = FISHY_PUBLIC_PATH . "/{$error_type}.html";
	
	if (file_exists($error_output)) {
		$content = file_get_contents($error_output);
		
		header("Content-Type: text/html");
		header("Content-Length: " . strlen($content));
		
		echo $content;
		
		log_error("error_{$error_type}", $exception->__toString());
		
		die;
	} else {
		throw $exception;
	}
}

/**
 * Simple class that doesn't anything
 */
class BlankObject
{
	public function __get($propertie) { return null; }
	public function __set($propertie, $value) {}
	public function __call($method, $arguments) {}
}