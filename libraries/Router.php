<?php

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
