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
	protected $_block_cache_stack;
	
	public final function __construct($route = null)
	{
		$this->_data = array();
		$this->_render = true;
		$this->_render_layout = true;
		$this->_page_cache = false;
		$this->_layout = null;
		$this->_cycle = array();
		$this->_cycle_it = 0;
		$this->_params = array_merge($_GET, $_POST);
		$this->_block_cache_stack = array();
		$this->_current_route = $route;
		
		//load helpers
		foreach (glob(FISHY_HELPERS_PATH . "/*.php") as $helper) {
			require_once $helper;
		}
		
		foreach (glob(FISHY_SLICES_PATH . "/*/app/helpers/*.php") as $helper) {
			require_once $helper;
		}
		
		$this->initialize();
	}
	
	protected function initialize() {}
	
	public static function build_view_path($controller, $action)
	{
		$sufix = '/' . str_replace('_', '/', strtolower($controller)) . '/' . $action . '.php';
		
		$files = glob(FISHY_SLICES_PATH . '/*/app/views' . $sufix);
		$files[] = FISHY_VIEWS_PATH . $sufix;
		
		return $files[0];
	}
	
	protected function classname($lowercase = true)
	{
		$name = substr(get_class($this), 0, -strlen('Controller'));
		
		if ($lowercase) {
			$name = strtolower($name);
		}
		
		return $name;
	}
	
	protected function check_for_haml($path)
	{
		$haml_path = $path . ".haml";
		
		if (!file_exists($haml_path)) return $path;
		
		$new_path = FISHY_CACHE_PATH . "/haml" . substr($path, strlen(FISHY_VIEWS_PATH));
		
		if (file_exists($new_path)) {
			$current = filemtime($haml_path);
			$old = filemtime($new_path);
			
			if ($current <= $old) return $new_path;
		}
		
		require_once FISHY_VENDOR_PATH . "/haml/lib/haml.php";
		
		$haml = new Haml();
		$generated = $haml->parse(file_get_contents($haml_path));
		
		Fishy_DirectoryHelper::mkdir($new_path, true);
		
		file_put_contents($new_path, $generated);
		
		return $new_path;
	}
	
	protected function view_path($action, $controller = null)
	{
		if ($controller === null) {
			$controller = $this->classname();
		}
		
		$path = self::build_view_path($controller, $action);
		$path = $this->check_for_haml($path);
		
		return $path;
	}
	
	public function get_current_route()
	{
		return $this->_current_route;
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
			dispatch_error(new Exception("Not found $method in " . $this->classname()), 404);
		}
		
		if (method_exists($this, $method)) {
			call_user_func_array(array($this, $method), $args);
		}
		
		if ($this->_render) {
			$this->render($method);
		}
		
		if ($this->_page_cache) {
			$data = ob_get_flush();
			
			$file = Fishy_Cache::cache(Fishy_Uri::get_querystring(), $data);
		}
	}
	
	/**
	 * Do an execution based on a route
	 *
	 * @param $route The route to be executed
	 * @return void
	 */
	public static function run($route) {
		global $CONTROLLER;
		
		$controller_name = Fishy_StringHelper::camelize($route['controller']) . 'Controller';
		$method = strtolower($route['action']);
		
		if (!class_exists($controller_name)) {
			dispatch_error(new Exception("Controller {$controller_name} doesn't exists"), 404);
		}
		
		$controller = new $controller_name($route);
		$controller->_current_route = $route;
		$controller->_params = array_merge($controller->_params, $route['params']);
		
		$CONTROLLER = $controller;
		
		$controller->execute($method);
	}
	
	protected function render_options()
	{
		return array(
			'return' => false,
			'controller' => $this->classname(),
			'as' => null,
			'layout' => $this->layout_file(),
			'locals' => array()
		);
	}
	
	protected function layout_file()
	{
		if ($this->_layout) {
			return $this->_layout;
		}
		
		$layout = $this->classname();
		$current_class = $this;
		
		while (!$this->layout_path($layout)) {
			$class = new ReflectionClass($current_class);
			$class = $class->getParentClass();
			
			if (!$class) {
				break;
			}
			
			$class = $class->getName();
			
			$current_class = new $class;
			$layout = $current_class->classname();
		}
		
		return $layout;
	}
	
	protected function layout_path($layout)
	{
		$layouts = glob(FISHY_SLICES_PATH . "/*/app/views/layouts/" . $layout . '.php');
		$layouts[] = FISHY_VIEWS_PATH . '/layouts/' . $layout . '.php';
		
		$layout = $layouts[0];
		$layout = $this->check_for_haml($layout);
		
		return file_exists($layout) ? $layout : null;
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
		
		$layout = $this->layout_path($options['layout']);
		
		if ($layout && $this->_render_layout) {
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
		
		if (preg_match("/^(\w+)\/(\w+)$/", $partial, $matches)) {
			$partial = $matches[2];
			$options["controller"] = $matches[1];
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
	public function redirect_to($path)
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
	public final function base_url($sulfix = '')
	{
		if (preg_match("/^([a-z]+):\/\//", $sulfix)) {
			return $sulfix;
		}
		
		return FISHY_BASE_URL . $sulfix;
	}
	
	/**
	 * This function get one internal url of site
	 *
	 * @param $sulfix Sulfix to be append at end of file
	 * @return string The final path
	 */
	public function site_url($sulfix = '')
	{
		return $this->base_url(FISHY_INDEX_PAGE . $sulfix);
	}
	
	/**
	 * This function get one public url of site
	 *
	 * @param $sulfix Sulfix to be append at end of file
	 * @return string The final path
	 */
	public function public_url($sulfix = '')
	{
		return $this->base_url($sulfix);
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
	 * 
	 */
	public function cache_block($identifier)
	{
		$this->_block_cache_stack[] = $identifier;
		
		ob_start();
	}
	
	public function cache_block_end()
	{
		$block_content = ob_get_clean();
		$identifier = array_pop($this->_block_cache_stack);
		
		$cache_path = FISHY_CACHE_PATH . "/{$identifier}.block";
		
		//check for cache
		if (file_exists($cache_path)) {
			echo file_get_contents($cache_path);
			return;
		}
		
		//generate cache
		$parsed_block = preg_replace("/<#(.*?)#>/ms", "<?\$1?>", $block_content);
		
		$tmp_path = tempnam(FISHY_TMP_PATH, "BC");
		file_put_contents($tmp_path, $parsed_block);
		
		ob_start();
		include($tmp_path);
		$cache = ob_get_clean();
		
		unlink($tmp_path);
		
		file_put_contents($cache_path, $cache);
		
		echo $cache;
	}
	
	/**
	 * Find a element at $_GET, or return a default value
	 *
	 * @param string $propertie The name of propertie
	 * @param mixed $default The default value
	 * @return mixed
	 */
	public function gp($propertie, $default = null)
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
	public function pp($propertie, $default = null)
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
	public function sp($propertie, $default = null)
	{
		return isset($_SESSION[$propertie]) ? $_SESSION[$propertie] : $default;
	}
	
	public function cycle()
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
	
	public function image_cache($model, $field, $id = null, $configuration = array(), $user_vars = array())
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
	
	public function url_to($params)
	{
		global $ROUTER;
		
		$route = "";
		
		if (is_string($params) && preg_match("/^@([a-z][a-z0-9_]*)(?:\/([a-z][a-z0-9_]*))?/i", $params, $matches)) {
			$p = array();
			
			if (isset($matches[2])) {
				$p['controller'] = $matches[1];
				$p['action'] = $matches[2];
			} else {
				$p['action'] = $matches[1];
			}
			
			$params = $p;
		}
		
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
		
		if (!preg_match("/^[a-z]+:\/\//", $route)) {
			$route = FISHY_BASE_URL . FISHY_INDEX_PAGE . $route;
		}
		
		return $route;
	}
	
	/**
	 * Get a request param
	 *
	 * @param string $name The name of parameter
	 * @param mixed $default The default value
	 * @return mixed
	 */
	public function param($name, $default = null)
	{
		return isset($this->_params[$name]) ? $this->_params[$name] : $default;
	}
	
	/**
	 * Display a Javascript alert and then redirect user to another page
	 * Note: this method only works with client javascript enabled,
	 *       don't use it if you are not sure that your client will be
	 *       with javascript enabled
	 *
	 * @param string $message Message to display for client
	 * @param mixed $redirect_to String or Array with path to use in redirect
	 */
	public function show_message_and_redirect_to($message, $redirect_to)
	{
		$message = preg_replace('/(\r\n|\r|\n)/', '\\n', $message);
		
		$this->_render = false;
		$url = $this->url_to($redirect_to);
		
		$template_file = dirname(__FILE__) . '/../misc/show_message_and_redirect_to_template.html';
		$template_data = array("message" => $message, "url" => $url);
		
		$template = file_get_contents($template_file);
		
		echo Fishy_StringHelper::simple_template($template, $template_data);
	}
	
	/**
	 * Test if the request method is a POST request
	 *
	 * @return boolean true if is a POST request, false otherwise
	 */
	public function post_request()
	{
		return $_SERVER['REQUEST_METHOD'] == "POST";
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
	
	public function __call($method, $args)
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
	
	/* VIEW HELPERS */
	
	public function stylesheet_tag($css, $attr = array())
	{
		if (!preg_match('/\.css$/', $css)) {
			$css .= '.css';
		}
		
		$path_sufix = "stylesheets/{$css}";
		$real_path = FISHY_PUBLIC_PATH . '/' . $path_sufix;
		$mtime = @filemtime($real_path);
		
		$path = $this->public_url("stylesheets/{$css}?{$mtime}");
		$attr = array_merge(array(
			"href" => $path,
			"media" => "screen",
			"rel" => "stylesheet", 
			"type" => "text/css"
		), $attr);
		
		$attr = $this->build_tag_attributes($attr);
		
		return "<link{$attr} />";
	}
	
	public function ie_stylesheet_tag($file)
	{
		$css = $this->stylesheet_tag($file);
		
		return "<!--[if IE]>" . $css . "<![endif]-->";
	}
	
	public function javascript_tag()
	{
		$args = func_get_args();
		$last = $args[count($args) - 1];
		
		if (is_array($last)) {
			$attr = array_pop($args);
		} else {
			$attr = array();
		}
		
		$buffer = "";
		
		foreach ($args as $js) {
			if (!preg_match('/\.js$/', $js)) {
				$js .= '.js';
			}
		
			if (preg_match("/^[a-z]+:\/\//", $js)) {
				$path = $js;
			} else {
				$path_sufix = "javascripts/{$js}";
				$real_path = FISHY_PUBLIC_PATH . '/' . $path_sufix;
				$mtime = @filemtime($real_path);
				$path = $this->public_url("javascripts/{$js}?{$mtime}");
			}
			
			$cur_attr = array_merge(array(
				"src" => $path,
				"type" => "text/javascript"
			), $attr);
		
			$cur_attr = $this->build_tag_attributes($cur_attr);
			
			$buffer .= "<script{$cur_attr}></script>\r\n";
		}
		
		return $buffer;
	}
	
	public function image_tag($url, $params = array())
	{
		$url = preg_match("/^[a-z]+:\/\//", $url) ? $url : $this->public_url("images/" . $url);
		
		$params = array_merge(array(
				"src" => $url
		), $params);
		
		$attr = $this->build_tag_attributes($params);
		$img = "<img{$attr} />";
		
		return $img;
	}
	
	public function link_to($label, $url, $params = array())
	{
		$params = array_merge(array(
			"title" => $label,
			"href" => $this->url_to($url)
		), $params);
		
		$attr = $this->build_tag_attributes($params);
		$img = "<a{$attr}>{$label}</a>";
		
		return $img;
	}
	
	private function build_tag_attributes($attributes)
	{
		$attr = "";
		
		foreach ($attributes as $key => $value) {
			$attr .= " {$key}=\"{$value}\"";
		}
		
		return $attr;
	}
}
