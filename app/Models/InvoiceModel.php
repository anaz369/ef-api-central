<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table      = 'tbl_invoices';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'participant_id', 'direction', 'document_type', 'cancelled_invoice_id',
        'environment', 'invoice_number', 'issue_date', 'due_date', 'currency',
        'supplier_name', 'supplier_peppol_id', 'buyer_name', 'buyer_peppol_id',
        'subtotal', 'discount_amount', 'charge_amount', 'tax_amount', 'total',
        'transmission_status', 'status', 'xml_file_path', 'raw_xml',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Direction
    const DIR_INBOUND  = 0;
    const DIR_OUTBOUND = 1;

    // Document type (PINT AE: no debit note)
    const DOC_INVOICE     = 0;
    const DOC_CREDIT_NOTE = 1;
    const DOC_SB_INVOICE  = 2;
    const DOC_SB_CREDIT   = 3;

    // Transmission status
    const TX_PENDING   = 0;
    const TX_SENT      = 1;
    const TX_DELIVERED = 2;
    const TX_FAILED    = 3;
    const TX_RECEIVED  = 4;

    // Status
    const STATUS_ACTIVE  = 1;
    const STATUS_DELETED = 3;

    public static $docTypeLabels = [
        self::DOC_INVOICE     => 'Invoice',
        self::DOC_CREDIT_NOTE => 'Credit Note',
        self::DOC_SB_INVOICE  => 'Self-Billing Invoice',
        self::DOC_SB_CREDIT   => 'Self-Billing Credit Note',
    ];

    public static $txLabels = [
        self::TX_PENDING   => ['Pending',   'yellow'],
        self::TX_SENT      => ['Sent',      'blue'],
        self::TX_DELIVERED => ['Delivered', 'green'],
        self::TX_FAILED    => ['Failed',    'red'],
        self::TX_RECEIVED  => ['Received',  'green'],
    ];

    /**
     * Admin list — joins participant name, optional filters.
     */
    public function getList(array $filters = []): array
    {
        $builder = $this->db->table('tbl_invoices i')
            ->select('i.*, p.name AS participant_name')
            ->join('tbl_participants p', 'p.id = i.participant_id', 'left')
            ->where('i.status !=', self::STATUS_DELETED)
            ->orderBy('i.created_at', 'DESC');

        if (!empty($filters['participant_id'])) {
            $builder->where('i.participant_id', (int) $filters['participant_id']);
        }
        if (isset($filters['direction']) && $filters['direction'] !== '') {
            $builder->where('i.direction', (int) $filters['direction']);
        }
        if (isset($filters['transmission_status']) && $filters['transmission_status'] !== '') {
            $builder->where('i.transmission_status', (int) $filters['transmission_status']);
        }
        if (!empty($filters['environment']) || $filters['environment'] === '0') {
            $builder->where('i.environment', (int) $filters['environment']);
        }

        return $builder->limit(500)->get()->getResultArray();
    }

    public function getStats(): array
    {
        $base = $this->db->table('tbl_invoices')->where('status !=', self::STATUS_DELETED);
        return [
            'total'    => (clone $base)->countAllResults(),
            'outbound' => (clone $base)->where('direction', self::DIR_OUTBOUND)->countAllResults(),
            'inbound'  => (clone $base)->where('direction', self::DIR_INBOUND)->countAllResults(),
            'pending'  => (clone $base)->where('transmission_status', self::TX_PENDING)->countAllResults(),
            'failed'   => (clone $base)->where('transmission_status', self::TX_FAILED)->countAllResults(),
        ];
    }
}
