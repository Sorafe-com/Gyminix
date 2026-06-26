<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\BranchModel;

class BranchService
{
    private BranchModel $branchModel;
    private AuditLogModel $auditModel;

    public function __construct()
    {
        $this->branchModel = new BranchModel();
        $this->auditModel  = new AuditLogModel();
    }

    public function listByGym(int $gymId, array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $builder = $this->branchModel->db->table('branches b');
        $builder->select('b.*, u.first_name, u.last_name')
            ->join('users u', 'u.id = b.manager_id', 'left')
            ->where('b.gym_id', $gymId)
            ->where('b.deleted_at IS NULL');

        if (!empty($filters['search'])) {
            $builder->like('b.name', $filters['search']);
        }
        if (isset($filters['status'])) {
            $builder->where('b.status', $filters['status']);
        }

        $total    = (clone $builder)->countAllResults(false);
        $branches = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        return [
            'data' => $branches,
            'meta' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'pages' => (int) ceil($total / $perPage)],
        ];
    }

    public function create(array $data, int $gymId, int $actorId): array
    {
        $data['gym_id'] = $gymId;
        if (!empty($data['schedule']) && is_array($data['schedule'])) {
            $data['schedule'] = json_encode($data['schedule']);
        }
        $id = $this->branchModel->insert($data, true);
        $this->auditModel->log(['user_id' => $actorId, 'gym_id' => $gymId, 'module' => 'branches', 'action' => 'create', 'record_id' => $id]);
        return $this->branchModel->find($id);
    }

    public function update(int $id, array $data, int $actorId, int $gymId): array
    {
        if (!empty($data['schedule']) && is_array($data['schedule'])) {
            $data['schedule'] = json_encode($data['schedule']);
        }
        $this->branchModel->update($id, $data);
        $this->auditModel->log(['user_id' => $actorId, 'gym_id' => $gymId, 'module' => 'branches', 'action' => 'update', 'record_id' => $id]);
        return $this->branchModel->find($id);
    }

    public function delete(int $id, int $actorId, int $gymId): void
    {
        $this->branchModel->delete($id);
        $this->auditModel->log(['user_id' => $actorId, 'gym_id' => $gymId, 'module' => 'branches', 'action' => 'delete', 'record_id' => $id]);
    }

    public function toggleStatus(int $id, int $actorId, int $gymId): array
    {
        $branch = $this->branchModel->find($id);
        $this->branchModel->update($id, ['status' => $branch['status'] == 1 ? 0 : 1]);
        $this->auditModel->log(['user_id' => $actorId, 'gym_id' => $gymId, 'module' => 'branches', 'action' => 'toggle_status', 'record_id' => $id]);
        return $this->branchModel->find($id);
    }
}
