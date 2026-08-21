<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migrate credentials from per-participant to per-user.
 *
 * An ERP (type-0 user like ABC Gulf) gets ONE set of API credentials
 * that covers all of their participants. The invoice request specifies
 * which participant is the sender via sender_peppol_id.
 *
 * Changes:
 *   tbl_participant_credentials — add user_id, make participant_id nullable
 *   tbl_api_tokens             — add user_id, make participant_id nullable
 *   tbl_api_logs               — add user_id
 */
class CredentialsPerUser extends Migration
{
    public function up()
    {
        // ── tbl_participant_credentials ──────────────────────────────────
        $this->db->query('ALTER TABLE tbl_participant_credentials
            ADD COLUMN user_id INT UNSIGNED NULL DEFAULT NULL
                COMMENT "FK to tbl_users — credential owner"
                AFTER id,
            ADD INDEX idx_cred_user (user_id),
            ADD INDEX idx_cred_user_env (user_id, environment)
        ');

        // Make participant_id nullable (kept for legacy / historical data)
        $this->db->query('ALTER TABLE tbl_participant_credentials
            MODIFY COLUMN participant_id INT UNSIGNED NULL DEFAULT NULL
        ');

        // ── tbl_api_tokens ───────────────────────────────────────────────
        $this->db->query('ALTER TABLE tbl_api_tokens
            ADD COLUMN user_id INT UNSIGNED NULL DEFAULT NULL
                COMMENT "FK to tbl_users — token owner"
                AFTER id,
            ADD INDEX idx_tok_user (user_id)
        ');

        // Make participant_id nullable
        $this->db->query('ALTER TABLE tbl_api_tokens
            MODIFY COLUMN participant_id INT UNSIGNED NULL DEFAULT NULL
        ');

        // ── tbl_api_logs ─────────────────────────────────────────────────
        $this->db->query('ALTER TABLE tbl_api_logs
            ADD COLUMN user_id INT UNSIGNED NULL DEFAULT NULL
                COMMENT "FK to tbl_users — which user made the call"
                AFTER id,
            ADD INDEX idx_log_user (user_id)
        ');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE tbl_participant_credentials
            DROP INDEX idx_cred_user,
            DROP INDEX idx_cred_user_env,
            DROP COLUMN user_id,
            MODIFY COLUMN participant_id INT UNSIGNED NOT NULL
        ');

        $this->db->query('ALTER TABLE tbl_api_tokens
            DROP INDEX idx_tok_user,
            DROP COLUMN user_id,
            MODIFY COLUMN participant_id INT UNSIGNED NOT NULL
        ');

        $this->db->query('ALTER TABLE tbl_api_logs
            DROP INDEX idx_log_user,
            DROP COLUMN user_id
        ');
    }
}
