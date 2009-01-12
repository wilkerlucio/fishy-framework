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
	}
}
