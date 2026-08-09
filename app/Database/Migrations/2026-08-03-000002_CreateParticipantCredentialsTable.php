<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateParticipantCredentialsTable extends Migration
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
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'client_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'client_secret_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'bcrypt hashed secret',
            ],
            'client_secret_preview' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'comment'    => 'last 6 chars for display e.g. ...ab12cd',
            ],
            // 0=development, 1=production
            'environment' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '0=development, 1=production',
            ],
            // 0=inactive, 1=active
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '0=inactive, 1=active',
            ],
            'last_used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('client_id');
        $this->forge->addForeignKey('participant_id', 'tbl_participants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_participant_credentials');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_participant_credentials');
    }
}
