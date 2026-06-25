<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'gym_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'module'      => ['type' => 'VARCHAR', 'constraint' => 60],
            'action'      => ['type' => 'VARCHAR', 'constraint' => 60],
            'record_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'old_data'    => ['type' => 'JSON', 'null' => true],
            'new_data'    => ['type' => 'JSON', 'null' => true],
            'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'  => ['type' => 'TEXT', 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['gym_id', 'user_id', 'module']);
        $this->forge->createTable('audit_logs');
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs');
    }
}
