<?php

/*
 * Copyright 2009 Wilker Lucio <wilkerlucio@gmail.com>
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

require_once dirname(__FILE__) . '/../../libraries/Router.php';

class RouteSpec extends Fishy_Router
{
	protected function setup()
	{
		$this->map_cart('cart/view/:id', array("controller" => "cart", "action" => "see"));
		$this->map_connect('shop/:id', array("controller" => "cart"));
		$this->map_connect('post/:day/:month/:year', array("controller" => "post", "action" => "view", "conditions" => array("day" => "/\d{2}/", "month" => "/\d{2}/", "year" => "/\d{4}/")));
		$this->map_connect('post/:a/:b/:c', array("controller" => "post", "action" => "other"));
		$this->map_connect(':controller/:action/:id');
		$this->map_connect(':controller/:action/:id.:format');
		$this->map_connect(':controller/:action');
		$this->map_connect(':controller/:action.:format');
	}
}

class DescribeRouter extends PHPSpec_Context
{
	public function itShouldGenerateAPregPatternFromBasePattern()
	{
		$router = new RouteSpec();
		
		$result = $router->create_match_pattern(':controller/:action/:id');
		
		$this->spec($result['pattern'])->should->be('/^([a-z0-9_-]+?)\/([a-z0-9_-]+?)\/([a-z0-9_-]+?)$/i');
		$this->spec($result['names'])->should->be(array('controller', 'action', 'id'));
	}
	
	public function itShouldMatchRouteDefinition()
	{
		$router = new RouteSpec();
		
		$info = $router->match('main/view');
		
		$this->spec($info['controller'])->should->be('main');
		$this->spec($info['action'])->should->be('view');
		$this->spec($info['format'])->should->be('html');
		
		$info = $router->match('main/view/1');
		
		$this->spec($info['controller'])->should->be('main');
		$this->spec($info['action'])->should->be('view');
		$this->spec($info['format'])->should->be('html');
		$this->spec($info['params']['id'])->should->be('1');
		
		$info = $router->match('main/view/1.js');
		
		$this->spec($info['controller'])->should->be('main');
		$this->spec($info['action'])->should->be('view');
		$this->spec($info['format'])->should->be('js');
		
		$info = $router->match('cart/view/1');
		
		$this->spec($info['controller'])->should->be('cart');
		$this->spec($info['action'])->should->be('see');
		
		$info = $router->match('post/12/01/1988');
		
		$this->spec($info['controller'])->should->be('post');
		$this->spec($info['action'])->should->be('view');
		$this->spec($info['params']['day'])->should->be('12');
		$this->spec($info['params']['month'])->should->be('01');
		$this->spec($info['params']['year'])->should->be('1988');
		
		$info = $router->match('post/12/name/1988');
		
		$this->spec($info['controller'])->should->be('post');
		$this->spec($info['action'])->should->be('other');
		$this->spec($info['params']['a'])->should->be('12');
		$this->spec($info['params']['b'])->should->be('name');
		$this->spec($info['params']['c'])->should->be('1988');
	}
	
	public function itShouldApplyADefaultActionIfNoGivenOne()
	{
		$router = new RouteSpec();
		
		$info = $router->match('shop/1');
		
		$this->spec($info['controller'])->should->be('cart');
		$this->spec($info['action'])->should->be('index');
		$this->spec($info['format'])->should->be('html');
	}
	
	public function itShouldGenerateANamedRoute()
	{
		$router = new RouteSpec();
		
		$this->spec($router->named_route('cart', array("id" => 20)))->should->be('cart/view/20');
	}
	
	public function itShouldDiscoveryACorrectRouteForParams()
	{
		$router = new RouteSpec();
		
		$this->spec($router->discovery_route('cart', 'index', array("id" => "30")))->should->be('shop/30');
		$this->spec($router->discovery_route('main', 'index'))->should->be('main/index');
		$this->spec($router->discovery_route('cart', 'see', array("id" => "30")))->should->be('cart/view/30');
		$this->spec($router->discovery_route('main', 'index', array("id" => "20", "format" => "js")))->should->be('main/index/20.js');
	}
}
