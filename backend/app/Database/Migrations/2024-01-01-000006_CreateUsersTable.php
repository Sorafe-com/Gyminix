<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'gym_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'branch_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'role_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'first_name'        => ['type' => 'VARCHAR', 'constraint' => 80],
            'last_name'         => ['type' => 'VARCHAR', 'constraint' => 80],
            'email'             => ['type' => 'VARCHAR', 'constraint' => 150],
            'username'          => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'password'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'phone'             => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'avatar'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_super_admin'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'status'            => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'failed_attempts'   => ['type' => 'TINYINT', 'default' => 0],
            'locked_until'      => ['type' => 'DATETIME', 'null' => true],
            'last_login'        => ['type' => 'DATETIME', 'null' => true],
            'remember_token'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->addKey(['gym_id', 'role_id']);
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
