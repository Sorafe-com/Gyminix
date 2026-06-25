<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\GymModel;
use App\Models\GymSettingModel;

class GymService
{
    private GymModel $gymModel;
    private GymSettingModel $settingModel;
    private AuditLogModel $auditModel;

    public function __construct()
    {
        $this->gymModel     = new GymModel();
        $this->settingModel = new GymSettingModel();
        $this->auditModel   = new AuditLogModel();
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $builder = $this->gymModel->builder();

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('name', $filters['search'])
                ->orLike('email', $filters['search'])
                ->groupEnd();
        }
        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        $total = (clone $builder)->countAllResults(false);
        $gyms  = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        return [
            'data' => $gyms,
            'meta' => [
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
                'pages'    => (int) ceil($total / $perPage),
            ],
        ];
    }

    public function create(array $data, int $userId): array
    {
        $gymId = $this->gymModel->insert($data, true);
        $this->auditModel->log(['user_id' => $userId, 'module' => 'gyms', 'action' => 'create', 'record_id' => $gymId, 'new_data' => json_encode($data)]);
        return $this->gymModel->find($gymId);
    }

    public function update(int $id, array $data, int $userId): array
    {
        $old = $this->gymModel->find($id);
        $this->gymModel->update($id, $data);
        $this->auditModel->log(['user_id' => $userId, 'module' => 'gyms', 'action' => 'update', 'record_id' => $id, 'old_data' => json_encode($old), 'new_data' => json_encode($data)]);
        return $this->gymModel->find($id);
    }

    public function toggleStatus(int $id, int $userId): array
    {
        $gym    = $this->gymModel->find($id);
        $newStatus = $gym['status'] == 1 ? 0 : 1;
        $this->gymModel->update($id, ['status' => $newStatus]);
        $this->auditModel->log(['user_id' => $userId, 'gym_id' => $id, 'module' => 'gyms', 'action' => 'toggle_status', 'record_id' => $id]);
        return $this->gymModel->find($id);
    }

    public function getSettings(int $gymId): array
    {
        return $this->settingModel->getByGym($gymId);
    }

    public function saveSettings(int $gymId, array $settings, int $userId): void
    {
        foreach ($settings as $group => $keys) {
            foreach ($keys as $key => $value) {
                $this->settingModel->setSetting($gymId, $key, (string) $value, $group);
            }
        }
        $this->auditModel->log(['user_id' => $userId, 'gym_id' => $gymId, 'module' => 'gym_settings', 'action' => 'update', 'new_data' => json_encode($settings)]);
    }
}
