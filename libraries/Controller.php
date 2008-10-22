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

abstract class Fishy_Controller
{
	protected $_current_route;
	protected $_data;
	protected $_render;
	protected $_render_layout;
	protected $_layout;
	protected $_page_cache;
	
	public final function __construct()
	{
		$this->_data = array();
		$this->_render = true;
		$this->_render_layout = true;
		$this->_page_cache = false;
		$this->_layout = $this->classname();
		
		$this->initialize();
	}
	
	protected function initialize() {}
	
	public static function build_view_path($controller, $action)
	{
		return FISHY_VIEWS_PATH . '/' . str_replace('_', '/', $controller) . '/' . $action . '.php';
	}
	
	protected function classname()
	{
		return strtolower(substr(get_class($this), 0, -strlen('Controller')));
	}
	
	protected function view_path($action, $controller = null)
	{
		if ($controller === null) {
			$controller = $this->classname();
		}
		
		return self::build_view_path($controller, $action);
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
		$view_path = $this->view_path($method);
		
		if (!method_exists($this, $method) && !file_exists($view_path)) {
			throw new Exception("Not found $method in " . $this->classname());
		}
		
		if (method_exists($this, $method)) {
			call_user_func_array(array($this, $method), $args);
		}
		
		if ($this->_render) {
			$this->render($method);
		}
		
		if ($this->_page_cache) {
			$data = ob_get_flush();
			$file = Fishy_Cache::cache($this->_current_route, $data);
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
		$controller->_current_route = $route;
		$controller->execute($method, $args);
	}
	
	protected function render_options()
	{
		return array(
			'return' => false,
			'controller' => $this->classname()
		);
	}
	
	/**
	 * Render the view of an action
	 *
	 * @action The action to be rendered
	 */
	protected function render($action, $options = array())
	{
		$options = array_merge($this->render_options(), $options);
		
		ob_start();
		
		$view_path = $this->view_path($action, $options['controller']);
		
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
		
		if ($options['return']) {
			return $output;
		} else {
			echo $output;
		}
	}
	
	/**
	 * Renderize a partial template
	 *
	 * @param $partial The name o partial template
	 * @param $data The data to send to partial template
	 * @param $return If you pass true to this parameter, the output will be returned instead of printed
	 * @return mixed
	 */
	protected function render_partial($partial, $data = null, $options = array())
	{
		$options = array_merge($this->render_options(), $options);
		
		ob_start();
		
		$view_path = $this->view_path("_$partial", $options['controller']);
		
		if (file_exists($view_path)) {
			include $view_path;
		}
		
		$output = ob_get_clean();
		
		if ($options['return']) {
			return $output;
		} else {
			echo $output;
		}
	}
	
	/**
	 * Renderize a collection into a partial template
	 *
	 * @param $partial The name o partial template
	 * @param $collection The collection to send to partial template
	 * @param $return If you pass true to this parameter, the output will be returned instead of printed
	 * @return mixed
	 */
	protected function render_collection($partial, $collection, $return = false)
	{
		$output = array();
		
		foreach ($collection as $data) {
			$output[] = $this->render_partial($partial, $data, array('return' => true));
		}
		
		$output = implode('', $output);
		
		if ($return) {
			return $output;
		} else {
			echo $output;
		}
	}
	
	/**
	 * Redirect current flow
	 *
	 * @param $path The path to be redirected, this path works like if you are using site_url() method
	 * @return void
	 */
	protected function redirect_to($path)
	{
		header('Location: ' . $this->site_url($path));
		exit;
	}
	
	/**
	 * This function get the base URL of site
	 *
	 * @param $sulfix Sulfix to be append at end of file
	 * @return string The final path
	 */
	protected final function base_url($sulfix = '')
	{
		return FISHY_BASE_URL . $sulfix;
	}
	
	/**
	 * This function get one internal url of site
	 *
	 * @param $sulfix Sulfix to be append at end of file
	 * @return string The final path
	 */
	protected function site_url($sulfix = '')
	{
		return $this->base_url(FISHY_INDEX_PAGE . $sulfix);
	}
	
	/**
	 * This function get one public url of site
	 *
	 * @param $sulfix Sulfix to be append at end of file
	 * @return string The final path
	 */
	protected function public_url($sulfix = '')
	{
		return $this->base_url('public/' . $sulfix);
	}
	
	/**
	 * Schedule cache when page load is finished
	 *
	 * @return void
	 */
	protected function cache_output()
	{
		$this->_page_cache = true;
		
		ob_start();
	}
	
	/**
	 * Find a element at $_GET, or return a default value
	 *
	 * @param string $propertie The name of propertie
	 * @param mixed $default The default value
	 * @return mixed
	 */
	protected function gp($propertie, $default = null)
	{
		return isset($_GET[$propertie]) ? $_GET[$propertie] : $default;
	}
	
	/**
	 * Find a element at $_POST, or return a default value
	 *
	 * @param string $propertie The name of propertie
	 * @param mixed $default The default value
	 * @return mixed
	 */
	protected function pp($propertie, $default = null)
	{
		return isset($_POST[$propertie]) ? $_POST[$propertie] : $default;
	}
	
	/**
	 * Find a element at $_SESSION, or return a default value
	 *
	 * @param string $propertie The name of propertie
	 * @param mixed $default The default value
	 * @return mixed
	 */
	protected function sp($propertie, $default = null)
	{
		return isset($_SESSION[$propertie]) ? $_SESSION[$propertie] : $default;
	}
	
	/**
	 * Get a value at data store
	 *
	 * @param $propertie The name o propertie
	 * @param $default The default value (if propertie doesn't exists)
	 * @return mixed
	 */
	protected function get($propertie, $default = null)
	{
		return isset($this->_data[$propertie]) ? $this->_data[$propertie] : $default;
	}
	
	protected function __get($propertie)
	{
		return $this->get($propertie);
	}
	
	protected function __set($propertie, $value)
	{
		$this->_data[$propertie] = $value;
	}
}
