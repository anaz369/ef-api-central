<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Rename tbl_participant_credentials → tbl_api_credentials.
 * Credentials belong to users, not participants.
 */
class RenameCredentialsTable extends Migration
{
    public function up(): void
    {
        $this->db->query('RENAME TABLE tbl_participant_credentials TO tbl_api_credentials');
    }

    public function down(): void
    {
        $this->db->query('RENAME TABLE tbl_api_credentials TO tbl_participant_credentials');
    }
}
