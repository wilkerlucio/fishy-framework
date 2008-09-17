<?php

class Fishy_Controller
{
	protected $_data;
	protected $_render;
	protected $_render_layout;
	
	public final function __construct()
	{
		$this->_data = array();
		$this->_render = true;
		$this->_render_layout = true;
		
		$this->initialize();
	}
	
	protected function initialize() {}
	
	protected function classname()
	{
		return strtolower(substr(get_class($this), 0, -strlen('Controller')));
	}
	
	public function execute($method, $args = array())
	{
		ob_start();
		
		call_user_func_array(array($this, $method), $args);
		
		$view = FISHY_VIEWS_PATH . '/' . $this->classname() . '/' . $method . '.php';
		
		if (file_exists($view)) {
			include $view;
		}
		
		$output = ob_get_clean();
		
		$layout = FISHY_VIEWS_PATH . '/layouts/' . $this->classname() . '.php';
		
		if (file_exists($layout) && $this->_render_layout) {
			$content = $output;
			ob_start();
			include $layout;
			$output = ob_get_clean();
		}
		
		echo $output;
	}
	
	public function run($route) {
		$args = explode('/', $route);
		
		$controller_name = Fishy_StringHelper::camelize(array_shift($args)) . 'Controller';
		$method = strtolower(array_shift($args));
		
		$controller = new $controller_name();
		$controller->execute($method, $args);
	}
	
	public function render()
	{
		$this->_render = false;
	}
	
	public function __get($propertie)
	{
		return $this->_data[$propertie];
	}
	
	public function __set($propertie, $value)
	{
		$this->_data[$propertie] = $value;
	}
}
