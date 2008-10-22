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

class Fishy_StringBuilder
{
	private $data;
	
	private $indent_level;
	private $indent_string;
	private $indent_cache;
	private $newline_string;
	
	public function __construct()
	{
		$this->data = '';
		
		$this->indent_level = 0;
		$this->indent_string = "\t";
		$this->indent_cache = '';
		$this->newline_string = "\n";
	}
	
	public function append($string)
	{
		$this->data .= $this->indent_cache . $string;
	}
	
	public function appendln($string)
	{
		$this->append($string . $this->newline_string);
	}
	
	public function increase_indent($level = 1)
	{
		$this->indent_level += $level;
		$this->refresh_ident_cache();
	}
	
	public function decrease_indent($level = 1)
	{
		$this->indent_level -= $level;
		$this->refresh_ident_cache();
	}
	
	private function refresh_ident_cache()
	{
		$this->indent_cache = str_repeat($this->indent_string, $this->indent_level);
	}
	
	public function get_data()
	{
		return $this->data;
	}
}
