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
			'action' => $this->default_action(),
			'format' => 'html',
			'params' => array()
		);
		
		$found = false;
		
		foreach ($this->routes as $route) {
			$matches = array();
			$data = array();
			$pattern = $route['match_pattern'];
			
			if (preg_match($pattern, $path, $matches)) {
				foreach ($route['names'] as $key => $name) {
					$data[$name] = $matches[$key + 1];
				}
				
				foreach (array('controller', 'action') as $value) {
					if ($route['options'][$value]) {
						$data[$value] = $route['options'][$value];
					}
				}
				
				foreach ($route['options']['defaults'] as $key => $value) {
					if (!isset($data[$key])) {
						$data[$key] = $value;
					}
				}
				
				$conditions_ok = true;
				
				foreach ($route['options']['conditions'] as $field => $condition) {
					if (!preg_match($condition, $data[$field])) {
						$conditions_ok = false;
						break;
					}
				}
				
				if ($conditions_ok) {
					//apply namespace if given
					if ($route['options']['namespace']) {
						$data['controller'] = $route['options']['namespace'] . '_' . $data['controller'];
					}
					
					$found = true;
					
					break;
				}
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
	
	/**
	 * Generate a route based in your name
	 *
	 * @param string $name The name of route to be generated
	 * @param array $params The params to create route
	 * @return string The generated route
	 */
	public function named_route($name, $params = array())
	{
		foreach ($this->routes as $route) {
			if ($route['name'] == $name) {
				return $this->apply_route($route['pattern'], $params);
			}
		}
		
		throw new Fishy_RouterException("Named route $name not found");
	}
	
	/**
	 * Discovery a route for certain parameters
	 *
	 * @param string $controller Controller name
	 * @param string $action Action name
	 * @param array $params The params
	 * @return string The discovered route
	 */
	public function discovery_route($controller, $action, $req_params = array())
	{
		foreach ($this->routes as $route) {
			$params = $req_params;
			$con = $controller;
			
			if ($route['options']['namespace']) {
				if (Fishy_StringHelper::starts_with($con, $route['options']['namespace'])) {
					$con = substr($con, strlen($route['options']['namespace']) + 1);
				} else {
					continue;
				}
			}
			
			//check if can solve controller
			if (in_array('controller', $route['names'])) {
				$params['controller'] = $con;
			} elseif ($route['options']['controller'] != $con) {
				continue;
			}
			
			//check if can solve action
			if (in_array('action', $route['names'])) {
				$params['action'] = $action;
			} else {
				if (!$route['options']['action']) {
					$route['options']['action'] = $this->default_action();
				}
				
				if ($route['options']['action'] != $action) {
					continue;
				}
			}
			
			if (count($params) != count($route['names'])) {
				continue;
			}
			
			$solve = true;
			
			//check if can solve params
			foreach ($params as $key => $param) {
				if ($key == 'controller' || $key == 'action') continue;
				
				if (!in_array($key, $route['names'])) {
					$solve = false;
					break;
				}
			}
			
			if ($solve) {
				return $this->apply_route($route['pattern'], $params);
			}
		}
		
		throw new Fishy_RouterException("The requested route can't be discovery");
	}
	
	private function apply_route($pattern, $params)
	{
		$var_buffer = '';
		$cur = 0;
		$max = strlen($pattern);
		$state = 0;
		$output = '';
		
		while ($cur < $max) {
			$char = $pattern[$cur];
			
			if ($state == 0) {
				if ($char == ':') {
					$var_buffer = '';
					$state = 1;
				} else {
					$output .= $char;
				}
			} elseif ($state == 1) {
				$code = ord($char);
				
				if ($char == '_' || ($code > 96 && $code < 123)) {
					$var_buffer .= $char;
				} else {
					$output .= $params[$var_buffer];
					$output .= $char;
					$state = 0;
				}
			}
			
			$cur++;
		}
		
		if ($state == 1) {
			$output .= $params[$var_buffer];
		}
		
		return $output;
	}
	
	public function __call($method, $args)
	{
		if (substr($method, 0, 4) == 'map_') {
			$name = substr($method, 4);
			
			$pattern = $args[0];
			
			if (count($args) > 1) {
				$options = $args[1];
			} else {
				$options = array();
			}
			
			$options['name'] = $name;
			
			return $this->map_connect($pattern, $options);
		}
		
		throw new Exception('Method not found');
	}
	
	private function default_options()
	{
		return array(
			'controller' => null,
			'action' => null,
			'name' => null,
			'conditions' => array(),
			'defaults' => array(),
			'namespace' => null
		);
	}
}

class Fishy_RouterException extends Exception {}
