<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MainSeeder extends Seeder
{
    public function run()
    {
        $this->call('GymSeeder');
        $this->call('RoleSeeder');
        $this->call('PermissionSeeder');
        $this->call('UserSeeder');
        $this->call('MenuSeeder');
    }
}
