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
	protected $_cycle;
	protected $_cycle_it;
	protected $_params;
	
	public final function __construct()
	{
		$this->_data = array();
		$this->_render = true;
		$this->_render_layout = true;
		$this->_page_cache = false;
		$this->_layout = $this->classname();
		$this->_cycle = array();
		$this->_cycle_it = 0;
		$this->_params = array_merge($_GET, $_POST);
		
		$this->initialize();
	}
	
	protected function initialize() {}
	
	public static function build_view_path($controller, $action)
	{
		return FISHY_VIEWS_PATH . '/' . str_replace('_', '/', strtolower($controller)) . '/' . $action . '.php';
	}
	
	protected function classname($lowercase = true)
	{
		$name = substr(get_class($this), 0, -strlen('Controller'));
		
		if ($lowercase) {
			$name = strtolower($name);
		}
		
		return $name;
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
	public static function run($route) {
		$controller_name = Fishy_StringHelper::camelize($route['controller']) . 'Controller';
		$method = strtolower($route['action']);
		
		$controller = new $controller_name();
		$controller->_current_route = $route;
		$controller->_params = array_merge($controller->_params, $route['params']);
		$controller->execute($method);
	}
	
	protected function render_options()
	{
		return array(
			'return' => false,
			'controller' => $this->classname(),
			'as' => null,
			'layout' => $this->_layout,
			'locals' => array()
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
		
		$layout = FISHY_VIEWS_PATH . '/layouts/' . $options['layout'] . '.php';
		
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
		
		if ($options['as'] === null) {
			$options['as'] = $partial;
		}
		
		ob_start();
		
		$view_path = $this->view_path("_$partial", $options['controller']);
		
		if (file_exists($view_path)) {
			//instance locals
			foreach ($options['locals'] as $key => $value) {
				$$key = $value;
			}
			
			$$options['as'] = $data;
			
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
	protected function render_collection($partial, $collection, $options = array())
	{
		$output = array();
		$options = array_merge($this->render_options(), $options);
		$return = $options['return'];
		$options['return'] = true;
		
		foreach ($collection as $data) {
			$output[] = $this->render_partial($partial, $data, $options);
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
		header('Location: ' . $this->url_to($path));
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
	
	protected function cycle()
	{
		$args = func_get_args();
		
		if ($args !== $this->_cycle) {
			$this->_cycle = $args;
			$this->_cycle_it = 0;
		} else {
			$this->_cycle_it++;
			
			if ($this->_cycle_it >= count($this->_cycle)) {
				$this->_cycle_it = 0;
			}
		}
		
		return $this->_cycle[$this->_cycle_it];
	}
	
	protected function image_cache($model, $field, $id = null, $configuration = array(), $user_vars = array())
	{
		$configuration = array_merge(array(
			'width' => 100,
			'height' => 100,
			'mode' => 1,
			'format' => 'jpg',
			'path_format' => '#cache/#model/#field/#id.#width.#height.#mode.#format',
			'default' => ''
		), $configuration);
		
		$vars = array_merge(array(
			'cache' => FISHY_CACHE_PATH,
			'model' => $model,
			'field' => $field,
			'id' => $id,
			'width' => $configuration['width'],
			'height' => $configuration['height'],
			'mode' => $configuration['mode'],
			'format' => $configuration['format']
		), $user_vars);
		
		$path = Fishy_StringHelper::simple_template($configuration['path_format'], $vars);
		
		$object = is_a($model, 'ActiveRecord') ? $model : ActiveRecord::model($model)->find($id);
		
		if ($object->$field) {
			if (file_exists($path) && file_exists($object->$field) && filemtime($object->$field) > filemtime($path)) {
				unlink($path);
			}
			
			if (!file_exists($path)) {
				if ($object->$field) {
					Fishy_DirectoryHelper::mkdir($path, true);
					
					$image = new Fishy_Image($object->$field);
					$image->resize($configuration['width'], $configuration['height'], $configuration['mode']);
					$image->save($path);
				}
			}
		} else {
			$this->redirect_to('public/' . $configuration['default']);
		}
		
		return file_get_contents($path);
	}
	
	protected function url_to($params)
	{
		global $ROUTER;
		
		$route = "";
		
		if (is_array($params)) {
			$params = array_merge(array("controller" => $this->classname()), $params);
			
			if (!isset($params['action'])) {
				$params['action'] = $ROUTER->default_action();
			}
			
			$controller = $params['controller'];
			$action = $params['action'];
			
			unset($params['controller']);
			unset($params['action']);
			
			$route = $ROUTER->discovery_route($controller, $action, $params);
		} else {
			$route = $params;
		}
		
		return FISHY_BASE_URL . FISHY_INDEX_PAGE . $route;
	}
	
	/**
	 * Get a request param
	 *
	 * @param string $name The name of parameter
	 * @param mixed $default The default value
	 * @return mixed
	 */
	protected function param($name, $default = null)
	{
		return isset($this->_params[$name]) ? $this->_params[$name] : $default;
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
	
	protected function __call($method, $args)
	{
		global $ROUTER;
		
		if (Fishy_StringHelper::ends_with($method, '_url')) {
			$route_name = substr($method, 0, -4);
			
			return FISHY_BASE_URL . FISHY_INDEX_PAGE . $ROUTER->named_route($route_name, @$args[0]);
		}
		
		if (Fishy_StringHelper::starts_with($method, 'redirect_to_')) {
			$route_name = substr($method, strlen('redirect_to_'));
			
			return $this->redirect_to($ROUTER->named_route($route_name, @$args[0]));
		}
		
		throw new Exception("Method $method doesn't exists");
	}
}
