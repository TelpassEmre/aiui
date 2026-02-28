<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuditService;
use App\Services\DatabaseManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CorrectionController
{
    public function __construct(
        private DatabaseManager $dbManager,
        private AuditService $audit
    ) {}

    public function index(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->dbManager->loadDynamicConnections();
        $connections = $this->dbManager->getActiveConnections();

        $params = $request->getQueryParams();
        $dbName = $params['db'] ?? 'aivoice';

        // Düzeltme bekleyen (completed ve transcript dolu) kayıtlar
        $jobs = $this->dbManager->connection($dbName)
            ->table('stt_jobs')
            ->where('status', 'completed')
            ->whereNotNull('transcript')
            ->orderByDesc('created_at')
            ->paginate(20);

        ob_start();
        include BASE_PATH . '/app/Views/correction/index.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->dbManager->loadDynamicConnections();

        $params = $request->getQueryParams();
        $dbName = $params['db'] ?? 'aivoice';
        $id     = (int)$args['id'];

        $job = $this->dbManager->connection($dbName)->table('stt_jobs')->find($id);

        ob_start();
        include BASE_PATH . '/app/Views/correction/edit.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->dbManager->loadDynamicConnections();

        $data      = $request->getParsedBody();
        $dbName    = $data['db'] ?? 'aivoice';
        $id        = (int)$args['id'];
        $newText   = trim($data['transcript'] ?? '');

        $job = $this->dbManager->connection($dbName)->table('stt_jobs')->find($id);

        if ($job && $newText) {
            $this->dbManager->connection($dbName)
                ->table('stt_jobs')
                ->where('id', $id)
                ->update(['transcript' => $newText]);

            $this->audit->log('correction', "stt_jobs:{$dbName}", ['transcript' => $job->transcript], ['transcript' => $newText]);
        }

        return $response->withHeader('Location', "/correction?db={$dbName}")->withStatus(302);
    }
}
