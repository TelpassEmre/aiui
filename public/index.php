<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_START', microtime(true));

require BASE_PATH . '/vendor/autoload.php';

// Timezone
$config = require BASE_PATH . '/config/app.php';
date_default_timezone_set($config['app']['timezone'] ?? 'Europe/Istanbul');

// Hata gösterimi
if (($config['app']['debug'] ?? false) === true) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// DI Container
$container = require BASE_PATH . '/app/bootstrap/container.php';

// Slim App
$app = \DI\Bridge\Slim\Bridge::create($container);

// Middleware
require BASE_PATH . '/app/bootstrap/middleware.php';

// Routes
require BASE_PATH . '/app/bootstrap/routes.php';

$app->run();
