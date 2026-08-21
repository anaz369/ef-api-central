<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Links each participant to the user (ERP or direct client) that manages them.
 *
 * NULL = participant added directly by Ethicfin super admin (no managing user).
 * SET  = participant belongs to a type-0 user (e.g. ABC Gulf ERP account).
 *
 * Nullable so existing rows and super-admin-managed participants are unaffected.
 */
class AddUserIdToParticipants extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE tbl_participants
            ADD COLUMN user_id INT UNSIGNED NULL DEFAULT NULL
                COMMENT "FK to tbl_users — managing ERP/client user (NULL = super-admin managed)"
                AFTER id,
            ADD INDEX idx_part_user (user_id)
        ');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE tbl_participants
            DROP INDEX idx_part_user,
            DROP COLUMN user_id
        ');
    }
}
