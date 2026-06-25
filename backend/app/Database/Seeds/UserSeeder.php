<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('users')->insertBatch([
            [
                'gym_id'         => null,
                'branch_id'      => null,
                'role_id'        => 1,
                'first_name'     => 'Super',
                'last_name'      => 'Admin',
                'email'          => 'superadmin@gyminix.com',
                'username'       => 'superadmin',
                'password'       => password_hash('Admin123!', PASSWORD_BCRYPT),
                'is_super_admin' => 1,
                'status'         => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'gym_id'         => 1,
                'branch_id'      => null,
                'role_id'        => 2,
                'first_name'     => 'Admin',
                'last_name'      => 'Demo',
                'email'          => 'admin@demo.com',
                'username'       => 'admin',
                'password'       => password_hash('Admin123!', PASSWORD_BCRYPT),
                'is_super_admin' => 0,
                'status'         => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ]);
    }
}
