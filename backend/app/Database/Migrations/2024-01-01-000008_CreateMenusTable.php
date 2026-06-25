<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenusTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'parent_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 80],
            'slug'          => ['type' => 'VARCHAR', 'constraint' => 80],
            'route'         => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'icon'          => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'permission_slug' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'order'         => ['type' => 'INT', 'default' => 0],
            'status'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('parent_id');
        $this->forge->createTable('menus');
    }

    public function down()
    {
        $this->forge->dropTable('menus');
    }
}
