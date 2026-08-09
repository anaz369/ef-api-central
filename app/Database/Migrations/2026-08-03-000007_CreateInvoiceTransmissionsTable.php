<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoiceTransmissionsTable extends Migration
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
            'invoice_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            // 0=created, 1=submitted, 2=ap_received, 3=smp_lookup, 4=delivered, 5=failed
            'event' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'comment'    => '0=created, 1=submitted, 2=ap_received, 3=smp_lookup, 4=delivered, 5=failed',
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // 0=info, 1=success, 2=error
            'level' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '0=info, 1=success, 2=error',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('invoice_id', 'tbl_invoices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_invoice_transmissions');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_invoice_transmissions');
    }
}
