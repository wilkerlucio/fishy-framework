<?php

/*
 * Copyright 2009 Wilker Lucio <wilkerlucio@gmail.com>
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

class Fishy_Zip
{
	private $ref;
	private $content_data;
	
	public function __construct($path)
	{
		if (!is_file($path)) {
			throw new Fishy_Zip_Exception("The file $path doesn't exists");
		}
		
		$this->ref = zip_open($path);
		
		$this->read_data();
	}
	
	private function read_data()
	{
		$data = array();
		
		while ($entry = zip_read($this->ref)) {
			$item = array(
				"ref"               => $entry,
				"name"              => zip_entry_name($entry),
				"filesize"          => zip_entry_filesize($entry),
				"compressedsize"    => zip_entry_compressedsize($entry),
				"compressionmethod" => zip_entry_compressionmethod($entry)
			);
			
			$name = $item["name"];
			
			$item["type"] = $name[strlen($name) - 1] == '/' ? 'folder' : 'file';
			
			$data[] = $item;
		}
		
		$this->content_data = $data;
	}
	
	private function find_entry_by_name($name)
	{
		foreach ($this->content_data as $entry) {
			if ($entry["name"] == $name) {
				return $entry;
			}
		}
		
		return false;
	}
	
	public function list_of_files()
	{
		$files = array();
		
		foreach ($this->content_data as $entry) {
			if ($entry["type"] == "file") {
				$files[] = $entry["name"];
			}
		}
		
		return $files;
	}
	
	public function extract($file, $to = '.')
	{
		$to = trim($to, '/');
		
		$entry = $this->find_entry_by_name($file);
		
		if ($entry !== false) {
			file_put_contents($to . '/' . $file, zip_entry_read($entry["ref"]));
			
			return true;
		}
		
		return false;
	}
	
	public function extract_all($path = '.')
	{
		$path = trim($path, '/');
		
		foreach ($this->content_data as $entry) {
			if ($entry["type"] == "file") {
				file_put_contents($path . '/' . $entry["name"], zip_entry_read($entry["ref"]));
			} else {
				mkdir($path . '/' . $entry["name"]);
			}
		}
	}
	
	public function close()
	{
		zip_close($this->ref);
		
		$this->ref = null;
	}
}

class Fishy_Zip_Exception extends Exception {}
