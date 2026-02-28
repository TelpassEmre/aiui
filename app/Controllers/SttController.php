<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\DatabaseManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SttController
{
    public function __construct(private DatabaseManager $dbManager) {}

    public function index(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->dbManager->loadDynamicConnections();
        $connections = $this->dbManager->getActiveConnections();

        ob_start();
        include BASE_PATH . '/app/Views/stt/index.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    }

    /**
     * DataTables server-side AJAX
     */
    public function data(Request $request, Response $response): Response
    {
        $this->dbManager->loadDynamicConnections();
        $params  = $request->getQueryParams();
        $dbName  = $params['db'] ?? 'aivoice';
        $draw    = (int)($params['draw'] ?? 1);
        $start   = (int)($params['start'] ?? 0);
        $length  = (int)($params['length'] ?? 25);
        $search  = $params['search']['value'] ?? '';
        $status  = $params['status'] ?? '';
        $lang    = $params['lang'] ?? '';

        try {
            $db    = $this->dbManager->connection($dbName);
            $query = $db->table('stt_jobs');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('caller_id', 'like', "%{$search}%")
                      ->orWhere('job_id', 'like', "%{$search}%")
                      ->orWhere('transcript', 'like', "%{$search}%");
                });
            }
            if ($status) $query->where('status', $status);
            if ($lang)   $query->where('language_detected', $lang);

            $total    = $db->table('stt_jobs')->count();
            $filtered = $query->count();
            $records  = $query->orderByDesc('created_at')->skip($start)->take($length)->get();

            $data = $records->map(fn($r) => [
                'id'             => $r->id,
                'job_id'         => $r->job_id,
                'caller_id'      => $r->caller_id,
                'status'         => $r->status,
                'language'       => $r->language_detected,
                'word_count'     => $r->word_count,
                'avg_confidence' => $r->avg_confidence ? round($r->avg_confidence * 100, 1) . '%' : '-',
                'profanity'      => $r->profanity_flag ? '⚠️' : '',
                'underage'       => $r->underage_flag ? '🔞' : '',
                'created_at'     => $r->created_at,
            ])->toArray();

            $result = [
                'draw'            => $draw,
                'recordsTotal'    => $total,
                'recordsFiltered' => $filtered,
                'data'            => $data,
            ];
        } catch (\Exception $e) {
            $result = ['draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => $e->getMessage()];
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->dbManager->loadDynamicConnections();

        $params = $request->getQueryParams();
        $dbName = $params['db'] ?? 'aivoice';
        $id     = (int)$args['id'];

        $job = $this->dbManager->connection($dbName)->table('stt_jobs')->find($id);

        ob_start();
        include BASE_PATH . '/app/Views/stt/show.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    }
}
