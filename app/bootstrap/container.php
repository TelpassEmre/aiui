<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Predis\Client as RedisClient;

$builder = new ContainerBuilder();

$builder->addDefinitions([

    // Config
    'config' => function () {
        return require BASE_PATH . '/config/app.php';
    },

    // Logger
    Logger::class => function () {
        $log = new Logger('aiui');
        $log->pushHandler(new StreamHandler(
            BASE_PATH . '/logs/app.log',
            Logger::DEBUG
        ));
        return $log;
    },

    // Local DB (Eloquent Capsule)
    Capsule::class => function ($c) {
        $config  = $c->get('config');
        $dbConf  = require BASE_PATH . '/config/database.php';
        $capsule = new Capsule();

        // Local bağlantı
        $capsule->addConnection($dbConf['connections']['local'], 'local');

        // Remote aivoice
        $capsule->addConnection($dbConf['connections']['aivoice'], 'aivoice');

        // Remote application
        $capsule->addConnection($dbConf['connections']['application'], 'application');

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    },

    // Redis
    RedisClient::class => function ($c) {
        $config = $c->get('config');
        return new RedisClient([
            'scheme'   => 'tcp',
            'host'     => $config['redis']['host'],
            'port'     => $config['redis']['port'],
            'password' => $config['redis']['password'],
            'database' => $config['redis']['database'],
        ]);
    },

    // Services
    \App\Services\DatabaseManager::class => \DI\autowire(),
    \App\Services\SupervisorService::class => \DI\autowire(),
    \App\Services\AuditService::class => \DI\autowire(),

    // Controllers
    \App\Controllers\AuthController::class       => \DI\autowire(),
    \App\Controllers\DashboardController::class  => \DI\autowire(),
    \App\Controllers\SttController::class        => \DI\autowire(),
    \App\Controllers\CorrectionController::class => \DI\autowire(),
    \App\Controllers\SettingsController::class   => \DI\autowire(),
    \App\Controllers\SupervisorController::class => \DI\autowire(),
    \App\Controllers\UserController::class       => \DI\autowire(),
]);

return $builder->build();
