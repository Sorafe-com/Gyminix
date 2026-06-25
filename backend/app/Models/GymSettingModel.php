<?php

namespace App\Models;

use CodeIgniter\Model;

class GymSettingModel extends Model
{
    protected $table            = 'gym_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['gym_id', 'key', 'value', 'group'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    public function getByGym(int $gymId): array
    {
        $rows = $this->where('gym_id', $gymId)->findAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['group']][$row['key']] = $row['value'];
        }
        return $result;
    }

    public function setSetting(int $gymId, string $key, string $value, string $group = 'general'): void
    {
        $existing = $this->where('gym_id', $gymId)->where('key', $key)->first();
        if ($existing) {
            $this->update($existing['id'], ['value' => $value, 'group' => $group]);
        } else {
            $this->insert(['gym_id' => $gymId, 'key' => $key, 'value' => $value, 'group' => $group]);
        }
    }
}
