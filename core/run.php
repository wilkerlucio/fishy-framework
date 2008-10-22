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

//set some system definitios
define('FISHY_SYSTEM_CLASS_PREFIX', 'Fishy_');

//load core exceptions
require_once FISHY_SYSTEM_CORE_PATH . '/core_exceptions.php';

//autoloader for classes
include_once FISHY_SYSTEM_CORE_PATH . '/autoloader.php';

//load uri
$current_uri = Fishy_Uri::get_querystring();

//load router configuration
$router_conf = include FISHY_CONFIG_PATH . '/router.php';

$ROUTER = new Fishy_Router($router_conf->get_data());
$current_route = $ROUTER->parse($current_uri);

//check for cache
Fishy_Cache::page_cache($current_route);

//load configuration basics
$conf = include FISHY_CONFIG_PATH . '/config.php';

define('FISHY_BASE_URL', $conf->base_url);
define('FISHY_INDEX_PAGE', $conf->index_page);

//some simple usefull helpers functions
require_once FISHY_SYSTEM_CORE_PATH . '/core_helpers.php';

//disable magic quotes
include_once FISHY_SYSTEM_CORE_PATH . '/magic_quotes.php';

//transform upload format
include_once FISHY_SYSTEM_CORE_PATH . '/upload_transform.php';

//load database
require_once FISHY_SYSTEM_DATABASE_PATH . '/ActiveRecord.php';

$db_conf = include FISHY_CONFIG_PATH . '/db.php';

FieldAct::set_upload_path(FISHY_UPLOAD_PATH . '/');
DBCommand::configure($db_conf->host, $db_conf->user, $db_conf->password, $db_conf->database);

//run!
Fishy_Controller::run($current_route);
