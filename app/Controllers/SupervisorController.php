<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\SupervisorService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SupervisorController
{
    public function __construct(private SupervisorService $supervisor) {}

    public function index(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $processes = $this->supervisor->getProcesses();
        ob_start(); include BASE_PATH . '/app/Views/supervisor/index.php'; $html = ob_get_clean();
        $response->getBody()->write($html); return $response;
    }

    public function action(Request $request, Response $response): Response
    {
        $data    = $request->getParsedBody();
        $process = $data['process'] ?? '';
        $action  = $data['action'] ?? '';

        $allowed = ['start', 'stop', 'restart'];
        if (!in_array($action, $allowed) || empty($process)) {
            $response->getBody()->write(json_encode(['success' => false, 'error' => 'Geçersiz işlem']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $this->supervisor->{$action}($process);
            $response->getBody()->write(json_encode(['success' => true]));
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function logs(Request $request, Response $response, array $args): Response
    {
        $name  = $args['name'] ?? '';
        $lines = (int)($request->getQueryParams()['lines'] ?? 50);
        $logs  = $this->supervisor->getLogs($name, $lines);
        $response->getBody()->write(json_encode(['logs' => $logs]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
