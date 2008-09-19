<?php

class Fishy_Controller
{
	protected $_data;
	protected $_render;
	protected $_render_layout;
	protected $_layout;
	
	public final function __construct()
	{
		$this->_data = array();
		$this->_render = true;
		$this->_render_layout = true;
		$this->_layout = $this->classname();
		
		$this->initialize();
	}
	
	protected function initialize() {}
	
	protected function classname()
	{
		return strtolower(substr(get_class($this), 0, -strlen('Controller')));
	}
	
	/**
	 * Execute main routine of an action
	 *
	 * @param $method The action to be executed
	 * @param $args The arguments to pass to action
	 * @return void
	 */
	public function execute($method, $args = array())
	{
		$view_path = FISHY_VIEWS_PATH . '/' . $this->classname() . '/' . $method . '.php';
		
		if (!method_exists($this, $method) && !file_exists($view_path)) {
			throw new Exception("Not found $method in " . $this->classname());
		}
		
		if (method_exists($this, $method)) {
			call_user_func_array(array($this, $method), $args);
		}
		
		if ($this->_render) {
			$this->render($method);
		}
	}
	
	/**
	 * Do an execution based on a route
	 *
	 * @param $route The route to be executed
	 * @return void
	 */
	public function run($route) {
		$args = explode('/', $route);
		
		$controller_name = Fishy_StringHelper::camelize(array_shift($args)) . 'Controller';
		$method = strtolower(array_shift($args));
		
		$controller = new $controller_name();
		$controller->execute($method, $args);
	}
	
	/**
	 * Render the view of an action
	 *
	 * @action The action to be rendered
	 */
	public function render($action, $return = false)
	{
		ob_start();
		
		$view_path = FISHY_VIEWS_PATH . '/' . $this->classname() . '/' . $action . '.php';
		
		if (file_exists($view_path)) {
			include $view_path;
		}

		$output = ob_get_clean();

		$layout = FISHY_VIEWS_PATH . '/layouts/' . $this->_layout . '.php';

		if (file_exists($layout) && $this->_render_layout) {
			$content = $output;
			ob_start();
			include $layout;
			$output = ob_get_clean();
		}
		
		$this->_render = false;

		if ($return) {
			return $output;
		} else {
			echo $output;
		}
	}
	
	/**
	 * Get a value at data store
	 *
	 * @param $propertie The name o propertie
	 * @param $default The default value (if propertie doesn't exists)
	 * @return mixed
	 */
	public function get($propertie, $default = null)
	{
		return isset($this->_data[$propertie]) ? $this->_data[$propertie] : $default;
	}
	
	public function __get($propertie)
	{
		return $this->get($propertie);
	}
	
	public function __set($propertie, $value)
	{
		$this->_data[$propertie] = $value;
	}
}
