<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGymSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'gym_id'     => ['type' => 'INT', 'unsigned' => true],
            'key'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'value'      => ['type' => 'TEXT', 'null' => true],
            'group'      => ['type' => 'VARCHAR', 'constraint' => 60, 'default' => 'general'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['gym_id', 'key']);
        $this->forge->createTable('gym_settings');
    }

    public function down()
    {
        $this->forge->dropTable('gym_settings');
    }
}
