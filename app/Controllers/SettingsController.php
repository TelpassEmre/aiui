<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuditService;
use App\Services\DatabaseManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SettingsController
{
    public function __construct(
        private DatabaseManager $dbManager,
        private AuditService $audit
    ) {}

    private function appDb() {
        return $this->dbManager->connection('application');
    }

    public function index(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $settings = $this->appDb()->table('app_settings')->orderBy('category')->get();
        ob_start(); include BASE_PATH . '/app/Views/settings/index.php'; $html = ob_get_clean();
        $response->getBody()->write($html); return $response;
    }

    public function update(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $data = $request->getParsedBody();
        foreach ($data['settings'] ?? [] as $id => $value) {
            $old = $this->appDb()->table('app_settings')->find($id);
            $this->appDb()->table('app_settings')->where('id', $id)->update(['setting_value' => $value]);
            $this->audit->log('settings_update', "app_settings:{$id}", ['value' => $old->setting_value], ['value' => $value]);
        }
        return $response->withHeader('Location', '/settings')->withStatus(302);
    }

    public function env(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $vars = $this->appDb()->table('env_variables')->orderBy('category')->get();
        ob_start(); include BASE_PATH . '/app/Views/settings/env.php'; $html = ob_get_clean();
        $response->getBody()->write($html); return $response;
    }

    public function envUpdate(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id   = (int)$args['id'];
        $data = $request->getParsedBody();
        $old  = $this->appDb()->table('env_variables')->find($id);
        $this->appDb()->table('env_variables')->where('id', $id)->update(['env_value' => $data['env_value'] ?? '']);
        $this->audit->log('env_update', "env_variables:{$id}", ['value' => $old->env_value], ['value' => $data['env_value']]);
        return $response->withHeader('Location', '/settings/env')->withStatus(302);
    }

    public function ml(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $models = $this->appDb()->table('ml_model_config')->get();
        ob_start(); include BASE_PATH . '/app/Views/settings/ml.php'; $html = ob_get_clean();
        $response->getBody()->write($html); return $response;
    }

    public function mlUpdate(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id   = (int)$args['id'];
        $data = $request->getParsedBody();
        $this->appDb()->table('ml_model_config')->where('id', $id)->update([
            'is_active'       => isset($data['is_active']) ? 1 : 0,
            'min_confidence'  => $data['min_confidence'] ?? 0.70,
            'timeout_seconds' => $data['timeout_seconds'] ?? 30,
            'parameters'      => $data['parameters'] ?? null,
        ]);
        return $response->withHeader('Location', '/settings/ml')->withStatus(302);
    }

    public function servers(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $servers = $this->appDb()->table('server_config')->get();
        $status  = $this->appDb()->table('service_status')->orderBy('server_id')->get();
        ob_start(); include BASE_PATH . '/app/Views/settings/servers.php'; $html = ob_get_clean();
        $response->getBody()->write($html); return $response;
    }

    public function features(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $flags = $this->appDb()->table('feature_flags')->get();
        ob_start(); include BASE_PATH . '/app/Views/settings/features.php'; $html = ob_get_clean();
        $response->getBody()->write($html); return $response;
    }

    public function featureToggle(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id   = (int)$args['id'];
        $flag = $this->appDb()->table('feature_flags')->find($id);
        if ($flag) {
            $newVal = $flag->is_enabled ? 0 : 1;
            $this->appDb()->table('feature_flags')->where('id', $id)->update(['is_enabled' => $newVal]);
            $this->audit->log('feature_toggle', "feature_flags:{$id}", ['is_enabled' => $flag->is_enabled], ['is_enabled' => $newVal]);
        }
        $response->getBody()->write(json_encode(['success' => true, 'is_enabled' => $newVal ?? 0]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function connections(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $connections = $this->dbManager->getActiveConnections();
        ob_start(); include BASE_PATH . '/app/Views/settings/connections.php'; $html = ob_get_clean();
        $response->getBody()->write($html); return $response;
    }

    public function connectionStore(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $data = $request->getParsedBody();
        \Illuminate\Database\Capsule\Manager::connection('local')->table('db_connections')->insert([
            'name'          => $data['name'],
            'display_name'  => $data['display_name'],
            'host'          => $data['host'],
            'port'          => $data['port'] ?? 3306,
            'username'      => $data['username'],
            'password'      => $data['password'],
            'database_name' => $data['database_name'],
            'color'         => $data['color'] ?? '#3B82F6',
            'is_active'     => 1,
            'sort_order'    => $data['sort_order'] ?? 0,
        ]);
        return $response->withHeader('Location', '/settings/connections')->withStatus(302);
    }

    public function connectionDelete(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        \Illuminate\Database\Capsule\Manager::connection('local')->table('db_connections')->where('id', $args['id'])->delete();
        return $response->withHeader('Location', '/settings/connections')->withStatus(302);
    }
}
