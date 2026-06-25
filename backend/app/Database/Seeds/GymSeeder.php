<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GymSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('gyms')->insert([
            'name'            => 'Gyminix Demo',
            'legal_name'      => 'Gyminix S.A.C.',
            'tax_id'          => '20123456789',
            'address'         => 'Av. Principal 123, Lima',
            'phone'           => '+51 999 000 111',
            'email'           => 'demo@gyminix.com',
            'website'         => 'https://gyminix.com',
            'primary_color'   => '#1a73e8',
            'secondary_color' => '#34a853',
            'currency'        => 'PEN',
            'currency_symbol' => 'S/',
            'timezone'        => 'America/Lima',
            'language'        => 'es',
            'date_format'     => 'd/m/Y',
            'status'          => 1,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
    }
}
