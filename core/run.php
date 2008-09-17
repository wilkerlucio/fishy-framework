<?php

//set some system definitios
define('FISHY_SYSTEM_CLASS_PREFIX', 'Fishy_');

$timestart = microtime(true);

//autoloader for classes
include_once FISHY_SYSTEM_CORE_PATH . '/autoloader.php';

//load uri
$current_uri = Fishy_Uri::get_querystring();

//load router configuration
$router_conf = include FISHY_CONFIG_PATH . '/router.php';

$router = new Fishy_Router($router_conf->get_data());
$current_route = $router->parse($current_uri);

Fishy_Controller::run($current_route);

echo '<br />Library load time: ' . (microtime(true) - $timestart) . '<br />';
