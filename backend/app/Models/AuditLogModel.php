<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'gym_id', 'user_id', 'module', 'action', 'record_id',
        'old_data', 'new_data', 'ip_address', 'user_agent', 'description',
    ];
    protected $useTimestamps = true;
    protected $updatedField  = '';

    public function log(array $data): void
    {
        $request = service('request');
        $this->insert(array_merge([
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s'),
        ], $data));
    }
}
