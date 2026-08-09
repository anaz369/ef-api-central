<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPostalZoneToParticipants extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_participants', [
            'postal_zone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'emirate',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_participants', 'postal_zone');
    }
}
