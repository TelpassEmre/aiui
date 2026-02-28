<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CorrectionController;
use App\Controllers\DashboardController;
use App\Controllers\SettingsController;
use App\Controllers\SttController;
use App\Controllers\SupervisorController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

// Auth rotaları (giriş gerektirmez)
$app->get('/login',           [AuthController::class, 'loginPage']);
$app->post('/login',          [AuthController::class, 'loginPost']);
$app->get('/logout',          [AuthController::class, 'logout']);

// Korumalı rotalar
$app->group('', function ($group) {

    // Dashboard
    $group->get('/',           [DashboardController::class, 'index']);
    $group->get('/dashboard',  [DashboardController::class, 'index']);
    $group->get('/dashboard/stats', [DashboardController::class, 'stats']); // AJAX

    // STT Jobs
    $group->get('/stt',              [SttController::class, 'index']);
    $group->get('/stt/data',         [SttController::class, 'data']);       // DataTables AJAX
    $group->get('/stt/{id}',         [SttController::class, 'show']);

    // Metin Düzeltme
    $group->get('/correction',       [CorrectionController::class, 'index']);
    $group->get('/correction/{id}',  [CorrectionController::class, 'edit']);
    $group->post('/correction/{id}', [CorrectionController::class, 'update']);

    // Settings
    $group->get('/settings',              [SettingsController::class, 'index']);
    $group->post('/settings',             [SettingsController::class, 'update']);
    $group->get('/settings/env',          [SettingsController::class, 'env']);
    $group->post('/settings/env/{id}',    [SettingsController::class, 'envUpdate']);
    $group->get('/settings/ml',           [SettingsController::class, 'ml']);
    $group->post('/settings/ml/{id}',     [SettingsController::class, 'mlUpdate']);
    $group->get('/settings/servers',      [SettingsController::class, 'servers']);
    $group->get('/settings/features',     [SettingsController::class, 'features']);
    $group->post('/settings/features/{id}/toggle', [SettingsController::class, 'featureToggle']);
    $group->get('/settings/connections',  [SettingsController::class, 'connections']);
    $group->post('/settings/connections', [SettingsController::class, 'connectionStore']);
    $group->post('/settings/connections/{id}/delete', [SettingsController::class, 'connectionDelete']);

    // Supervisor
    $group->get('/supervisor',            [SupervisorController::class, 'index']);
    $group->post('/supervisor/action',    [SupervisorController::class, 'action']);
    $group->get('/supervisor/logs/{name}',[SupervisorController::class, 'logs']);

    // Kullanıcı Yönetimi (sadece admin)
    $group->group('/users', function ($group) {
        $group->get('',          [UserController::class, 'index']);
        $group->get('/create',   [UserController::class, 'create']);
        $group->post('/create',  [UserController::class, 'store']);
        $group->get('/{id}',     [UserController::class, 'edit']);
        $group->post('/{id}',    [UserController::class, 'update']);
        $group->post('/{id}/delete', [UserController::class, 'destroy']);
    })->add(new RoleMiddleware('admin'));

})->add(AuthMiddleware::class);
