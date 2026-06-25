<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBranchesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'gym_id'       => ['type' => 'INT', 'unsigned' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'address'      => ['type' => 'TEXT', 'null' => true],
            'phone'        => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'email'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'schedule'     => ['type' => 'JSON', 'null' => true],
            'manager_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'status'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('gym_id');
        $this->forge->createTable('branches');
    }

    public function down()
    {
        $this->forge->dropTable('branches');
    }
}
