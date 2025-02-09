<?php
session_start();

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));
require_once '../vendor/autoload.php';
require_once '../app/config/config.php';
require_once '../Core/Router.php';

// Instantiate and set up routing
$router = new Core\Router();

// Define routes
$router->add('404', ['controller' => 'ErrorController', 'action' => 'notFound', 'protected' => false]);
$router->add('/', ['controller' => 'LoginController', 'action' => 'index', 'protected' => false]);
$router->add('/login', ['controller' => 'LoginController', 'action' => 'index', 'protected' => false]);
$router->add('/login/process', ['controller' => 'LoginController', 'action' => 'process', 'protected' => false]);
$router->add('/profile', ['controller' => 'ProfileController', 'action' => 'index', 'protected' => true]);
$router->add('/profile/changePassword', ['controller' => 'ProfileController', 'action' => 'changePassword', 'protected' => true]);
$router->add('/settings/shopify', ['controller' => 'ShopifyController', 'action' => 'index', 'protected' => true]);
$router->add('/settings/shopify/update', ['controller' => 'ShopifyController', 'action' => 'update', 'protected' => true]);
$router->add('/settings/netsuite', ['controller' => 'NetSuiteController', 'action' => 'index', 'protected' => true]);
$router->add('/settings/netsuite/update', ['controller' => 'NetSuiteController', 'action' => 'update', 'protected' => true]);
$router->add('/mapping', ['controller' => 'MappingController', 'action' => 'index', 'protected' => true]);
$router->add('/cron', ['controller' => 'CronController', 'action' => 'run', 'protected' => false]);
$router->add('/logout', ['controller' => 'LoginController', 'action' => 'logout', 'protected' => true]);
$router->add('/shopify_webhook', ['controller' => 'ShopifyController', 'action' => 'handleWebhook', 'protected' => false]);


// Dispatch the route
if (!$router->dispatch($_SERVER['REQUEST_URI'])) {
    // $router->dispatch('404');
}
