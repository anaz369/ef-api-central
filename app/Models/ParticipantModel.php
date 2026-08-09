<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantModel extends Model
{
    protected $table      = 'tbl_participants';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name', 'email', 'phone', 'country', 'trn', 'trade_license',
        'legal_form', 'tin_id', 'address_line1', 'address_line2',
        'city', 'emirate', 'postal_zone', 'peppol_scheme', 'peppol_id', 'peppol_participant_id',
        'integration_mode', 'status', 'tin_verified', 'peppol_verified',
        'production_access',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Status constants
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE   = 1;
    const STATUS_PENDING  = 2;
    const STATUS_DELETED  = 3;

    // Integration mode constants
    const MODE_MANAGED = 0;
    const MODE_SELF    = 1;

    public function getActive()
    {
        return $this->where('status !=', self::STATUS_DELETED)->findAll();
    }

    /**
     * Look up a Peppol participant in the ethicfin DB by TRN.
     * Returns the row or null if not found.
     */
    public function lookupPeppolByTrn(string $trn): ?array
    {
        if (!$trn) return null;
        return $this->db->query(
            'SELECT id, peppol_id, entity_name_en, status
               FROM ethicfin.tbl_peppol_participants
              WHERE vat_trn = ? LIMIT 1',
            [$trn]
        )->getRowArray() ?: null;
    }

    /**
     * Fetch a Peppol participant record by its ID from the ethicfin DB.
     */
    public function getPeppolById(int $id): ?array
    {
        return $this->db->query(
            'SELECT id, peppol_id, entity_name_en, status, linked_at
               FROM ethicfin.tbl_peppol_participants
              WHERE id = ? LIMIT 1',
            [$id]
        )->getRowArray() ?: null;
    }

    public function getStats()
    {
        return [
            'total'      => $this->where('status !=', self::STATUS_DELETED)->countAllResults(),
            'active'     => $this->where('status', self::STATUS_ACTIVE)->countAllResults(),
            'pending'    => $this->where('status', self::STATUS_PENDING)->countAllResults(),
            'production' => $this->where('production_access', 1)->countAllResults(),
        ];
    }
}
