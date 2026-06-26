<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;

class RoleService
{
    private RoleModel $roleModel;
    private PermissionModel $permModel;
    private RolePermissionModel $rpModel;
    private AuditLogModel $auditModel;

    public function __construct()
    {
        $this->roleModel  = new RoleModel();
        $this->permModel  = new PermissionModel();
        $this->rpModel    = new RolePermissionModel();
        $this->auditModel = new AuditLogModel();
    }

    public function listRoles(?int $gymId): array
    {
        if ($gymId === null) {
            return $this->roleModel->db->table('roles')
                ->where('deleted_at IS NULL')
                ->get()->getResultArray();
        }
        return $this->roleModel->db->table('roles')
            ->groupStart()
                ->where('gym_id', $gymId)
                ->orWhere('gym_id IS NULL')
            ->groupEnd()
            ->where('deleted_at IS NULL')
            ->get()->getResultArray();
    }

    public function createRole(array $data, int $gymId, int $actorId): array
    {
        $data['gym_id'] = $gymId;
        $data['slug']   = $this->slugify($data['name']);
        $id = $this->roleModel->insert($data, true);
        $this->auditModel->log(['user_id' => $actorId, 'gym_id' => $gymId, 'module' => 'roles', 'action' => 'create', 'record_id' => $id]);
        return $this->roleModel->find($id);
    }

    public function updateRole(int $id, array $data, int $actorId, int $gymId): array
    {
        if (!empty($data['name'])) {
            $data['slug'] = $this->slugify($data['name']);
        }
        $this->roleModel->update($id, $data);
        $this->auditModel->log(['user_id' => $actorId, 'gym_id' => $gymId, 'module' => 'roles', 'action' => 'update', 'record_id' => $id]);
        return $this->roleModel->find($id);
    }

    public function deleteRole(int $id, int $actorId, int $gymId): void
    {
        $this->roleModel->delete($id);
        $this->auditModel->log(['user_id' => $actorId, 'gym_id' => $gymId, 'module' => 'roles', 'action' => 'delete', 'record_id' => $id]);
    }

    public function listPermissions(): array
    {
        $perms = $this->permModel->findAll();
        $grouped = [];
        foreach ($perms as $p) {
            $grouped[$p['module']][] = $p;
        }
        return $grouped;
    }

    public function getRolePermissions(int $roleId): array
    {
        return array_column($this->rpModel->getPermissionsByRole($roleId), 'id');
    }

    public function syncRolePermissions(int $roleId, array $permissionIds, int $actorId, int $gymId): void
    {
        $this->rpModel->syncPermissions($roleId, $permissionIds);
        $this->auditModel->log(['user_id' => $actorId, 'gym_id' => $gymId, 'module' => 'roles', 'action' => 'sync_permissions', 'record_id' => $roleId]);
    }

    private function slugify(string $text): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '_', trim($text)));
    }
}
