<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;

class DatabaseManager
{
    private array $dynamicConnections = [];

    public function __construct(private Capsule $capsule) {}

    /**
     * db_connections tablosundaki tüm aktif bağlantıları yükler
     */
    public function loadDynamicConnections(): void
    {
        try {
            $connections = $this->capsule->getConnection('local')
                ->table('db_connections')
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->get();

            foreach ($connections as $conn) {
                $this->addConnection($conn->name, [
                    'driver'    => 'mysql',
                    'host'      => $conn->host,
                    'port'      => $conn->port,
                    'database'  => $conn->database_name,
                    'username'  => $conn->username,
                    'password'  => $conn->password,
                    'charset'   => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix'    => '',
                ]);
                $this->dynamicConnections[$conn->name] = [
                    'display_name' => $conn->display_name,
                    'color'        => $conn->color,
                ];
            }
        } catch (\Exception $e) {
            // Local DB henüz hazır değilse sessizce geç
        }
    }

    public function addConnection(string $name, array $config): void
    {
        $this->capsule->addConnection($config, $name);
    }

    public function connection(string $name = 'local'): Connection
    {
        return $this->capsule->getConnection($name);
    }

    /**
     * Tüm aktif remote DB listesi (dashboard için)
     */
    public function getActiveConnections(): array
    {
        try {
            return $this->capsule->getConnection('local')
                ->table('db_connections')
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Bağlantı test et
     */
    public function testConnection(array $config): bool
    {
        try {
            $tempName = 'test_' . uniqid();
            $this->capsule->addConnection(array_merge(['driver' => 'mysql', 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'prefix' => ''], $config), $tempName);
            $this->capsule->getConnection($tempName)->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
