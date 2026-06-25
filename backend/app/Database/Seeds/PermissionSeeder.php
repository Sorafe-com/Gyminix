<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $now  = date('Y-m-d H:i:s');
        $defs = [
            ['gyms',     'view'],
            ['gyms',     'create'],
            ['gyms',     'edit'],
            ['gyms',     'delete'],
            ['gyms',     'settings'],
            ['branches', 'view'],
            ['branches', 'create'],
            ['branches', 'edit'],
            ['branches', 'delete'],
            ['users',    'view'],
            ['users',    'create'],
            ['users',    'edit'],
            ['users',    'delete'],
            ['roles',    'view'],
            ['roles',    'create'],
            ['roles',    'edit'],
            ['roles',    'delete'],
            ['roles',    'permissions'],
            ['menus',    'view'],
            ['menus',    'create'],
            ['menus',    'edit'],
            ['menus',    'delete'],
            ['audit',    'view'],
            ['dashboard','view'],
        ];

        $rows = [];
        foreach ($defs as [$module, $action]) {
            $rows[] = [
                'module'      => $module,
                'action'      => $action,
                'slug'        => "{$module}.{$action}",
                'description' => ucfirst($action) . ' ' . $module,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        $this->db->table('permissions')->insertBatch($rows);

        // Grant all permissions to admin role (id=2)
        $permIds = $this->db->table('permissions')->select('id')->get()->getResultArray();
        $rp = array_map(fn($p) => ['role_id' => 2, 'permission_id' => $p['id']], $permIds);
        $this->db->table('role_permissions')->insertBatch($rp);
    }
}
