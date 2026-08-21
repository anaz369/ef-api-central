<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Performance indexes for high-volume multi-participant usage.
 *
 * Without composite indexes, every query like:
 *   SELECT * FROM tbl_invoices WHERE participant_id = 5 AND transmission_status = 3
 * does a full table scan — gets slow at 100k+ rows.
 *
 * With composite indexes starting with participant_id, MySQL filters to
 * only that participant's rows instantly regardless of total table size.
 */
class AddPerformanceIndexes extends Migration
{
    public function up()
    {
        // ── tbl_invoices ─────────────────────────────────────────────────────
        // Most common query patterns in admin list, dashboard stats, API responses

        // List invoices for a participant sorted by date (default view)
        $this->db->query('ALTER TABLE tbl_invoices
            ADD INDEX idx_inv_part_date       (participant_id, created_at),
            ADD INDEX idx_inv_part_status     (participant_id, transmission_status),
            ADD INDEX idx_inv_part_direction  (participant_id, direction),
            ADD INDEX idx_inv_part_env        (participant_id, environment),
            ADD INDEX idx_inv_part_inv_no     (participant_id, invoice_number),
            ADD INDEX idx_inv_issue_date      (participant_id, issue_date)
        ');

        // ── tbl_invoice_transmissions ────────────────────────────────────────
        // Queried by invoice_id to show event timeline — already has FK index,
        // but add created_at for ordered timeline queries

        $this->db->query('ALTER TABLE tbl_invoice_transmissions
            ADD INDEX idx_tx_inv_created (invoice_id, created_at)
        ');

        // ── tbl_api_logs ─────────────────────────────────────────────────────
        // Currently only has single-column participant_id index.
        // Logs grow fastest of all tables (every API call = one row).

        $this->db->query('ALTER TABLE tbl_api_logs
            ADD INDEX idx_log_part_date    (participant_id, created_at),
            ADD INDEX idx_log_part_status  (participant_id, status),
            ADD INDEX idx_log_part_env     (participant_id, environment)
        ');

        // ── tbl_api_tokens ───────────────────────────────────────────────────
        // token_hash lookup is hot path on every API request — already indexed,
        // but add composite for active token lookup

        $this->db->query('ALTER TABLE tbl_api_tokens
            ADD INDEX idx_tok_hash_active (token_hash, is_active, expires_at)
        ');

        // ── tbl_fta_onboard_details ──────────────────────────────────────────
        // Add participant_id FK — links an onboarded Peppol business to the
        // account (ERP or direct business) that manages them.
        // NULL = self-onboarded direct business (no managing account).

        $this->db->query('ALTER TABLE tbl_fta_onboard_details
            ADD COLUMN participant_id INT UNSIGNED NULL DEFAULT NULL
                COMMENT "FK to tbl_participants — managing account (ERP or direct business)"
                AFTER id,
            ADD INDEX idx_fta_participant (participant_id),
            ADD INDEX idx_fta_status_date (status, created_at)
        ');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE tbl_invoices
            DROP INDEX idx_inv_part_date,
            DROP INDEX idx_inv_part_status,
            DROP INDEX idx_inv_part_direction,
            DROP INDEX idx_inv_part_env,
            DROP INDEX idx_inv_part_inv_no,
            DROP INDEX idx_inv_issue_date
        ');

        $this->db->query('ALTER TABLE tbl_invoice_transmissions
            DROP INDEX idx_tx_inv_created
        ');

        $this->db->query('ALTER TABLE tbl_api_logs
            DROP INDEX idx_log_part_date,
            DROP INDEX idx_log_part_status,
            DROP INDEX idx_log_part_env
        ');

        $this->db->query('ALTER TABLE tbl_api_tokens
            DROP INDEX idx_tok_hash_active
        ');

        $this->db->query('ALTER TABLE tbl_fta_onboard_details
            DROP INDEX idx_fta_participant,
            DROP INDEX idx_fta_status_date,
            DROP COLUMN participant_id
        ');
    }
}
