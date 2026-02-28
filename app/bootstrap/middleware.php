<?php
declare(strict_types=1);

use Slim\Middleware\MethodOverrideMiddleware;

// Method override (_method=DELETE/PUT support)
$app->add(MethodOverrideMiddleware::class);

// Routing middleware
$app->addRoutingMiddleware();

// Error middleware
$config = require BASE_PATH . '/config/app.php';
$app->addErrorMiddleware(
    $config['app']['debug'] ?? false,
    true,
    true
);
