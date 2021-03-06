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

//this script changes the way of PHP handle multiple file uploads, read documentation for details

foreach ($_FILES as $key => $file) {
	//test against any file attribute
	if (is_array($file['name'])) {
		$transformed = array();
		
		foreach ($file as $param => $data) {
			foreach ($data as $index => $value) {
				$transformed[$index][$param] = $value;
			}
		}
		
		$_FILES[$key] = $transformed;
		
		//TODO: make a descente array merge
		
		if (!isset($_POST[$key])) {
			$_POST[$key] = array();
		}
		
		foreach ($transformed as $name => $item) {
			$_POST[$key][$name] = $item;
		}
	} else {
		$_POST[$key] = $file;
	}
}
