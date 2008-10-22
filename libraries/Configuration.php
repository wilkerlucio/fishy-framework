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

class Fishy_Configuration
{
	private $data;
	
	public function __construct()
	{
		$this->data = array();
	}
	
	public function get($propertie, $default = null)
	{
		return isset($this->data[$propertie]) ? $this->data[$propertie] : $default;
	}
	
	public function get_data()
	{
		return $this->data;
	}
	
	public function __get($propertie)
	{
		return $this->get($propertie);
	}
	
	public function __set($propertie, $value)
	{
		$this->data[$propertie] = $value;
	}
}
