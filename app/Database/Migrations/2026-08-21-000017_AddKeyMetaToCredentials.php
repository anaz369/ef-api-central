<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add key_name, key_description and request_status to tbl_participant_credentials.
 *
 * request_status tracks the API-key lifecycle:
 *   pending  — user requested, waiting for admin to generate
 *   active   — credentials issued and usable
 *   revoked  — manually revoked by user or admin
 *
 * Existing rows (admin-generated) default to 'active'.
 */
class AddKeyMetaToCredentials extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE tbl_participant_credentials
            ADD COLUMN key_name        VARCHAR(100)                          NULL DEFAULT NULL
                COMMENT 'Human-readable label given by the requesting user'
                AFTER user_id,
            ADD COLUMN key_description TEXT                                  NULL DEFAULT NULL
                COMMENT 'Optional description of intended use'
                AFTER key_name,
            ADD COLUMN request_status  ENUM('pending','active','revoked') NOT NULL DEFAULT 'active'
                COMMENT 'Lifecycle status of the credential request'
                AFTER key_description,
            ADD INDEX idx_cred_req_status (user_id, request_status)
        ");
    }

    public function down()
    {
        $this->db->query('ALTER TABLE tbl_participant_credentials
            DROP INDEX idx_cred_req_status,
            DROP COLUMN key_name,
            DROP COLUMN key_description,
            DROP COLUMN request_status
        ');
    }
}
