<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as Capsule;

class AuditService
{
    public function log(string $action, string $entity, ?array $oldValue = null, ?array $newValue = null): void
    {
        try {
            Capsule::connection('local')->table('audit_log')->insert([
                'user_id'     => $_SESSION['user_id'] ?? null,
                'action'      => $action,
                'entity'      => $entity,
                'old_value'   => $oldValue ? json_encode($oldValue) : null,
                'new_value'   => $newValue ? json_encode($newValue) : null,
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            // log hatası uygulamayı durdurmasın
        }
    }
}
