<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGymsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'             => ['type' => 'VARCHAR', 'constraint' => 150],
            'legal_name'       => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'tax_id'           => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'address'          => ['type' => 'TEXT', 'null' => true],
            'phone'            => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'email'            => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'website'          => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'logo'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'primary_color'    => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '#1a73e8'],
            'secondary_color'  => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '#34a853'],
            'currency'         => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'USD'],
            'currency_symbol'  => ['type' => 'VARCHAR', 'constraint' => 5, 'default' => '$'],
            'timezone'         => ['type' => 'VARCHAR', 'constraint' => 60, 'default' => 'America/Lima'],
            'language'         => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'es'],
            'date_format'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'd/m/Y'],
            'status'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('gyms');
    }

    public function down()
    {
        $this->forge->dropTable('gyms');
    }
}
