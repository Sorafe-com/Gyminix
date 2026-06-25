<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $now   = date('Y-m-d H:i:s');
        $roles = [
            ['gym_id' => null, 'name' => 'Super Administrador', 'slug' => 'super_admin',     'description' => 'Acceso total al sistema', 'is_system' => 1, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['gym_id' => 1,    'name' => 'Administrador',        'slug' => 'admin',           'description' => 'Administrador del gimnasio', 'is_system' => 1, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['gym_id' => 1,    'name' => 'Recepcionista',        'slug' => 'recepcionista',   'description' => 'Atención en recepción',     'is_system' => 1, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['gym_id' => 1,    'name' => 'Entrenador',           'slug' => 'entrenador',      'description' => 'Entrenador personal',       'is_system' => 1, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['gym_id' => 1,    'name' => 'Supervisor',           'slug' => 'supervisor',      'description' => 'Supervisor de área',        'is_system' => 1, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];
        $this->db->table('roles')->insertBatch($roles);
    }
}
