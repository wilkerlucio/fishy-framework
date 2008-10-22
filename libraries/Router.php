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

class Fishy_Router
{
	private $config;
	private $routes;
	
	public function __construct($config = array())
	{
		$this->config = $config;
		$this->routes = isset($config['routes']) ? $config['routes'] : array();
		
		$this->routes[] = array('(.*)', '$1');
	}
	
	public function parse($path)
	{
		foreach ($this->routes as $route) {
			list($search, $replace) = $route;
			$matches = array();
			
			if (preg_match('/' . $search . '/i', $path, $matches)) {
				$path = '';
				
				for ($i = 0; $i < strlen($replace); $i++) {
					if ($replace[$i] == '$') {
						$i++;
						
						if ($replace[$i] == '$') {
							$path .= '$';
						} else {
							$path .= $matches[$replace[$i]];
						}
					} else {
						$path .= $replace[$i];
					}
				}
				
				break;
			}
		}
		
		$path = trim($path, '/');
		
		if (!$path) {
			$path = $this->config['default_controller'];
		}
		
		$path = explode('/', $path);
		
		if (count($path) < 2) {
			$path[] = $this->config['default_action'];
		}
		
		return implode('/', $path);
	}
}
