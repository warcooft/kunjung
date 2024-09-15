<?php

namespace Aselsan\Visitors\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableVisitors extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        $fields = [
            'user_id'    => ['type' => 'int', 'null' => false],
            'visitor_id' => ['type' => 'int', 'null' => true],
            'ip_address' => ['type' => 'bigint', 'null' => true],
            'user_agent' => ['type' => 'varchar', 'constraint' => 255, 'default' => ''],
            'scheme'     => ['type' => 'varchar', 'constraint' => 15, 'default' => ''],
            'host'       => ['type' => 'varchar', 'constraint' => 63],
            'port'       => ['type' => 'varchar', 'constraint' => 15, 'default' => ''],
            'user'       => ['type' => 'varchar', 'constraint' => 31, 'default' => ''],
            'pass'       => ['type' => 'varchar', 'constraint' => 255, 'default' => ''],
            'path'       => ['type' => 'varchar', 'constraint' => 255],
            'query'      => ['type' => 'varchar', 'constraint' => 255, 'default' => ''],
            'fragment'   => ['type' => 'varchar', 'constraint' => 31, 'default' => ''],
            'views'      => ['type' => 'int', 'default' => 1],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
        ];

        $this->forge->addField('id');
        $this->forge->addField($fields);

        $this->forge->addKey('user_id');
        $this->forge->addKey('visitor_id');
        $this->forge->addKey('ip_address');
        $this->forge->addKey(['host', 'path']);
        $this->forge->addKey('created_at');
        $this->forge->addKey('updated_at');

        $this->forge->createTable('users_visitors');
    }

    /**
     * @return void
     */
    public function down()
    {
        $this->forge->dropTable('users_visitors');
    }
}
