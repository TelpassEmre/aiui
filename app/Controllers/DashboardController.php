<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\DatabaseManager;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController
{
    public function __construct(private DatabaseManager $dbManager) {}

    public function index(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->dbManager->loadDynamicConnections();
        $connections = $this->dbManager->getActiveConnections();

        ob_start();
        include BASE_PATH . '/app/Views/dashboard/index.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    }

    /**
     * AJAX — her DB için istatistik kartı verisi
     */
    public function stats(Request $request, Response $response): Response
    {
        $this->dbManager->loadDynamicConnections();
        $connections = $this->dbManager->getActiveConnections();
        $stats = [];

        foreach ($connections as $conn) {
            try {
                $db = $this->dbManager->connection($conn->name);

                $total      = $db->table('stt_jobs')->count();
                $completed  = $db->table('stt_jobs')->where('status', 'completed')->count();
                $pending    = $db->table('stt_jobs')->where('status', 'pending')->count();
                $processing = $db->table('stt_jobs')->where('status', 'processing')->count();
                $failed     = $db->table('stt_jobs')->where('status', 'failed')->count();

                // Dil dağılımı
                $languages = $db->table('stt_jobs')
                    ->selectRaw('language_detected, COUNT(*) as count')
                    ->whereNotNull('language_detected')
                    ->groupBy('language_detected')
                    ->get()->toArray();

                // Son 7 günlük trend
                $trend = $db->table('stt_jobs')
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', date('Y-m-d', strtotime('-7 days')))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()->toArray();

                $stats[] = [
                    'name'         => $conn->name,
                    'display_name' => $conn->display_name,
                    'color'        => $conn->color,
                    'total'        => $total,
                    'completed'    => $completed,
                    'pending'      => $pending,
                    'processing'   => $processing,
                    'failed'       => $failed,
                    'languages'    => $languages,
                    'trend'        => $trend,
                    'error'        => null,
                ];
            } catch (\Exception $e) {
                $stats[] = [
                    'name'         => $conn->name,
                    'display_name' => $conn->display_name,
                    'color'        => $conn->color,
                    'error'        => $e->getMessage(),
                ];
            }
        }

        $response->getBody()->write(json_encode($stats));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
