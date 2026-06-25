<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $menus = [
            ['parent_id' => null, 'name' => 'Dashboard',      'slug' => 'dashboard',  'route' => '/dashboard',        'icon' => 'fa fa-home',          'permission_slug' => 'dashboard.view', 'order' => 1,  'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['parent_id' => null, 'name' => 'Administración', 'slug' => 'admin',      'route' => null,                'icon' => 'fa fa-cogs',          'permission_slug' => null,             'order' => 2,  'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['parent_id' => 2,    'name' => 'Gimnasios',      'slug' => 'gyms',       'route' => '/gyms',             'icon' => 'fa fa-building',      'permission_slug' => 'gyms.view',      'order' => 1,  'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['parent_id' => 2,    'name' => 'Sucursales',     'slug' => 'branches',   'route' => '/branches',         'icon' => 'fa fa-map-marker',    'permission_slug' => 'branches.view',  'order' => 2,  'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['parent_id' => null, 'name' => 'Seguridad',      'slug' => 'security',   'route' => null,                'icon' => 'fa fa-shield',        'permission_slug' => null,             'order' => 3,  'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['parent_id' => 5,    'name' => 'Usuarios',       'slug' => 'users',      'route' => '/users',            'icon' => 'fa fa-users',         'permission_slug' => 'users.view',     'order' => 1,  'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['parent_id' => 5,    'name' => 'Roles',          'slug' => 'roles',      'route' => '/roles',            'icon' => 'fa fa-user-secret',   'permission_slug' => 'roles.view',     'order' => 2,  'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['parent_id' => 5,    'name' => 'Permisos',       'slug' => 'permissions','route' => '/permissions',      'icon' => 'fa fa-key',           'permission_slug' => 'roles.permissions','order' => 3, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['parent_id' => null, 'name' => 'Configuración',  'slug' => 'config',     'route' => null,                'icon' => 'fa fa-wrench',        'permission_slug' => null,             'order' => 4,  'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['parent_id' => 9,    'name' => 'Menús',          'slug' => 'menus',      'route' => '/menus',            'icon' => 'fa fa-bars',          'permission_slug' => 'menus.view',     'order' => 1,  'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['parent_id' => null, 'name' => 'Reportes',       'slug' => 'reports',    'route' => null,                'icon' => 'fa fa-bar-chart',     'permission_slug' => null,             'order' => 5,  'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['parent_id' => 11,   'name' => 'Auditoría',      'slug' => 'audit',      'route' => '/audit-logs',       'icon' => 'fa fa-history',       'permission_slug' => 'audit.view',     'order' => 1,  'status' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];
        $this->db->table('menus')->insertBatch($menus);
    }
}
