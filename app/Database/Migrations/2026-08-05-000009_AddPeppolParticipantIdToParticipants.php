<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPeppolParticipantIdToParticipants extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_participants', [
            'peppol_participant_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'peppol_id',
                'comment'    => 'Logical FK to ethicfin.tbl_peppol_participants.id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_participants', 'peppol_participant_id');
    }
}
