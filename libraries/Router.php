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
	private $routes;
	
	public function __construct()
	{
		$this->routes = array();
		
		$this->setup();
	}
	
	/**
	 * Override this method to apply your maps
	 */
	protected function setup() {}
	
	/**
	 * Try to match a given path into router rules
	 *
	 * @param string $path The path to be matched
	 *
	 * @return array Array with result information
	 */
	public function match($path)
	{
		$info = array(
			'controller' => null,
			'action' => null,
			'format' => 'html',
			'params' => array()
		);
		
		$found = false;
		
		foreach ($this->routes as $route) {
			$matches = array();
			$data = array();
			
			if (preg_match($route['match_pattern'], $path, $matches)) {
				foreach ($route['names'] as $key => $name) {
					$data[$name] = $matches[$key + 1];
				}
				
				$found = true;
				
				break;
			}
		}
		
		if (!$found) {
			throw new Fishy_RouterException('Route not found');
		}
		
		foreach ($data as $key => $value) {
			if (in_array($key, array('controller', 'action', 'format'))) {
				$info[$key] = $value;
			} else {
				$info['params'][$key] = $value;
			}
		}
		
		return $info;
	}
	
	private function escape_pattern_char($char)
	{
		$to_escape = './\\()[]?*+';
		
		if (strpos($to_escape, $char) !== false) {
			$char = '\\' . $char;
		}
		
		return $char;
	}
	
	/**
	 * Default action to be used
	 *
	 * @return string
	 */
	public function default_action()
	{
		return 'index';
	}
	
	/**
	 * Create a router rule
	 *
	 * @param string $pattern The pattern of rule
	 * @param array $options Options of rule definition
	 * @return void
	 */
	public function map_connect($pattern, $options = array())
	{
		$options = array_merge($this->default_options(), $options);
		
		$extract_match = $this->create_match_pattern($pattern);
		
		$route = array();
		$route['pattern'] = $pattern;
		$route['match_pattern'] = $extract_match['pattern'];
		$route['names'] = $extract_match['names'];
		$route['name'] = $options['name'];
		$route['options'] = $options;
		
		$this->routes[] = $route;
	}
	
	/**
	 * Evaluate a match pattern
	 *
	 * @param string $pattern Pattern to be evaluated
	 * @return array Data of evaluation
	 */
	public function create_match_pattern($pattern)
	{
		$var_buffer = '';
		$cur = 0;
		$max = strlen($pattern);
		$state = 0;
		$match_pattern = '/^';
		$names = array();
		
		while ($cur < $max) {
			$char = $pattern[$cur];
			
			if ($state == 0) {
				if ($char == ':') {
					$var_buffer = '';
					$state = 1;
				} else {
					$match_pattern .= $this->escape_pattern_char($char);
				}
			} elseif ($state == 1) {
				$code = ord($char);
				
				if ($char == '_' || ($code > 96 && $code < 123)) {
					$var_buffer .= $char;
				} else {
					$match_pattern .= '([a-z0-9_-]+?)';
					$match_pattern .= $this->escape_pattern_char($char);
					$state = 0;
					$names[] = $var_buffer;
					$var_buffer = '';
				}
			}
			
			$cur++;
		}
		
		if ($state == 1) {
			$match_pattern .= '([a-z0-9_-]+?)';
			$names[] = $var_buffer;
		}
		
		$match_pattern .= '$/i';
		
		return array("pattern" => $match_pattern, "names" => $names);
	}
	
	public function __call($method, $args)
	{
		//TODO: implement a easy way to created named routes
	}
	
	private function default_options()
	{
		return array(
			'controller' => null,
			'action' => null,
			'name' => null,
			'conditions' => array(),
			'defaults' => array(),
			'format' => 'html'
		);
	}
}

class Fishy_RouterException extends Exception {}
