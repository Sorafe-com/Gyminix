<?php

namespace App\Models;

use CodeIgniter\Model;

class RefreshTokenModel extends Model
{
    protected $table            = 'refresh_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'token', 'ip_address', 'user_agent', 'expires_at', 'revoked'];
    protected $useTimestamps    = true;
    protected $updatedField     = '';

    public function revokeAllByUser(int $userId): void
    {
        $this->where('user_id', $userId)->set('revoked', 1)->update();
    }

    public function findValid(string $token): ?array
    {
        return $this->where('token', $token)
            ->where('revoked', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->first();
    }
}
