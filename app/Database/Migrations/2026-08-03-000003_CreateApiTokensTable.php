<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiTokensTable extends Migration
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
            ],
            'credential_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'token_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            // 0=development, 1=production
            'environment' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '0=development, 1=production',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            // 0=inactive, 1=active
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '0=inactive, 1=active',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('token_hash');
        $this->forge->addForeignKey('participant_id', 'tbl_participants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('credential_id', 'tbl_participant_credentials', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_api_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_api_tokens');
    }
}
