<?php

namespace App\Models;

use CodeIgniter\Model;

class RolePermissionModel extends Model
{
    protected $table            = 'role_permissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['role_id', 'permission_id'];
    protected $useTimestamps    = false;

    public function getPermissionsByRole(int $roleId): array
    {
        return $this->db->table('role_permissions rp')
            ->select('p.*')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->get()->getResultArray();
    }

    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $this->where('role_id', $roleId)->delete();
        $rows = array_map(fn($pid) => ['role_id' => $roleId, 'permission_id' => $pid], $permissionIds);
        if (!empty($rows)) {
            $this->insertBatch($rows);
        }
    }
}
