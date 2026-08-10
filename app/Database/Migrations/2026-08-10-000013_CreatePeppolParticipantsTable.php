<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePeppolParticipantsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tin'            => ['type' => 'VARCHAR', 'constraint' => 20,   'null' => false],
            'vat_trn'        => ['type' => 'VARCHAR', 'constraint' => 30,   'null' => false],
            'peppol_id'      => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => false],
            'entity_name_en' => ['type' => 'VARCHAR', 'constraint' => 255,  'null' => false, 'default' => ''],
            'entity_name_ar' => ['type' => 'VARCHAR', 'constraint' => 255,  'null' => false, 'default' => ''],
            'legal_type'     => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => false, 'default' => ''],
            'effective_date' => ['type' => 'DATE',    'null' => true],
            'email'          => ['type' => 'VARCHAR', 'constraint' => 255,  'null' => false, 'default' => ''],
            'mobile'         => ['type' => 'VARCHAR', 'constraint' => 30,   'null' => false, 'default' => ''],
            'emaratax_token' => ['type' => 'VARCHAR', 'constraint' => 500,  'null' => false, 'default' => ''],
            'status'         => ['type' => 'ENUM',    'constraint' => ['active', 'delinked'], 'default' => 'active'],
            'linked_at'      => ['type' => 'DATETIME', 'null' => true],
            'delinked_at'    => ['type' => 'DATETIME', 'null' => true],
            'fta_response'   => ['type' => 'TEXT',    'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('tin');
        $this->forge->addKey('vat_trn');
        $this->forge->addKey('status');

        $this->forge->createTable('tbl_peppol_participants');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_peppol_participants', true);
    }
}
