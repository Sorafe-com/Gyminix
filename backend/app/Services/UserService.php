<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\UserModel;

class UserService
{
    private UserModel $userModel;
    private AuditLogModel $auditModel;

    public function __construct()
    {
        $this->userModel  = new UserModel();
        $this->auditModel = new AuditLogModel();
    }

    public function list(int $gymId, array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $builder = $this->userModel->builder('u');
        $builder->select('u.id, u.gym_id, u.branch_id, u.role_id, u.first_name, u.last_name, u.email, u.username, u.phone, u.avatar, u.is_super_admin, u.status, u.last_login, u.created_at, r.name as role_name, b.name as branch_name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->join('branches b', 'b.id = u.branch_id', 'left')
            ->where('u.gym_id', $gymId)
            ->where('u.deleted_at IS NULL');

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('u.first_name', $filters['search'])
                ->orLike('u.last_name', $filters['search'])
                ->orLike('u.email', $filters['search'])
                ->groupEnd();
        }
        if (isset($filters['status'])) {
            $builder->where('u.status', $filters['status']);
        }
        if (!empty($filters['role_id'])) {
            $builder->where('u.role_id', $filters['role_id']);
        }

        $total = (clone $builder)->countAllResults(false);
        $users = $builder->limit($perPage, ($page - 1) * $perPage)->orderBy('u.created_at', 'DESC')->get()->getResultArray();

        return [
            'data' => $users,
            'meta' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'pages' => (int) ceil($total / $perPage)],
        ];
    }

    public function create(array $data, int $actorId, int $gymId): array
    {
        $data['gym_id'] = $gymId;
        $userId = $this->userModel->insert($data, true);
        $this->auditModel->log(['user_id' => $actorId, 'gym_id' => $gymId, 'module' => 'users', 'action' => 'create', 'record_id' => $userId]);
        return $this->getById($userId);
    }

    public function update(int $id, array $data, int $actorId, int $gymId): array
    {
        $old = $this->userModel->find($id);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $this->userModel->update($id, $data);
        $this->auditModel->log(['user_id' => $actorId, 'gym_id' => $gymId, 'module' => 'users', 'action' => 'update', 'record_id' => $id, 'old_data' => json_encode($old)]);
        return $this->getById($id);
    }

    public function delete(int $id, int $actorId, int $gymId): void
    {
        $this->userModel->delete($id);
        $this->auditModel->log(['user_id' => $actorId, 'gym_id' => $gymId, 'module' => 'users', 'action' => 'delete', 'record_id' => $id]);
    }

    public function toggleStatus(int $id, int $actorId, int $gymId): array
    {
        $user = $this->userModel->find($id);
        $this->userModel->update($id, ['status' => $user['status'] == 1 ? 0 : 1]);
        $this->auditModel->log(['user_id' => $actorId, 'gym_id' => $gymId, 'module' => 'users', 'action' => 'toggle_status', 'record_id' => $id]);
        return $this->getById($id);
    }

    public function getById(int $id): array
    {
        return $this->userModel->db->table('users u')
            ->select('u.id, u.gym_id, u.branch_id, u.role_id, u.first_name, u.last_name, u.email, u.username, u.phone, u.avatar, u.is_super_admin, u.status, u.last_login, u.created_at, r.name as role_name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', $id)
            ->get()->getRowArray();
    }
}
