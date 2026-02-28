<?php
declare(strict_types=1);

namespace App\Services;

class SupervisorService
{
    private string $socket = 'unix:///var/run/supervisor.sock';

    public function getProcesses(): array
    {
        $output = $this->exec('supervisorctl status');
        $processes = [];

        foreach (explode("\n", trim($output)) as $line) {
            if (empty(trim($line))) continue;
            preg_match('/^(\S+)\s+(\S+)\s+pid\s+(\d+),\s+uptime\s+(.+)$/', $line, $m);
            if ($m) {
                $processes[] = [
                    'name'   => $m[1],
                    'status' => $m[2],
                    'pid'    => $m[3],
                    'uptime' => $m[4],
                ];
            } else {
                // stopped veya diğer durumlar
                $parts = preg_split('/\s+/', trim($line), 3);
                $processes[] = [
                    'name'   => $parts[0] ?? '',
                    'status' => $parts[1] ?? 'UNKNOWN',
                    'pid'    => null,
                    'uptime' => $parts[2] ?? '',
                ];
            }
        }

        return $processes;
    }

    public function start(string $name): bool
    {
        $this->exec("supervisorctl start " . escapeshellarg($name));
        return true;
    }

    public function stop(string $name): bool
    {
        $this->exec("supervisorctl stop " . escapeshellarg($name));
        return true;
    }

    public function restart(string $name): bool
    {
        $this->exec("supervisorctl restart " . escapeshellarg($name));
        return true;
    }

    public function getLogs(string $name, int $lines = 50): string
    {
        $logFile = "/var/www/aiui/logs/{$name}.log";
        if (!file_exists($logFile)) {
            return "Log dosyası bulunamadı: {$logFile}";
        }
        return $this->exec("tail -n {$lines} " . escapeshellarg($logFile));
    }

    private function exec(string $cmd): string
    {
        $output = shell_exec($cmd . ' 2>&1');
        return $output ?? '';
    }
}
