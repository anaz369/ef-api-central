<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'participant_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'credential_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'endpoint' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'method' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'request_body' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'response_body' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'response_code' => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => true,
            ],
            // 0=development, 1=production
            'environment' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '0=development, 1=production',
            ],
            // 0=failed, 1=success
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '0=failed, 1=success',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('participant_id');
        $this->forge->createTable('tbl_api_logs');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_api_logs');
    }
}
