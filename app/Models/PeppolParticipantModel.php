<?php

namespace App\Models;

use CodeIgniter\Model;

class PeppolParticipantModel extends Model
{
    protected $table      = 'tbl_peppol_participants';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'tin', 'vat_trn', 'peppol_id', 'entity_name_en', 'entity_name_ar',
        'legal_type', 'effective_date', 'email', 'mobile', 'emaratax_token',
        'status', 'linked_at', 'delinked_at', 'fta_response',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByTin(string $tin): ?array
    {
        return $this->where('tin', $tin)->first();
    }

    public function getByVatTrn(string $vatTrn): ?array
    {
        return $this->where('vat_trn', $vatTrn)->first();
    }

    public function saveParticipant(array $data): void
    {
        $existing = $this->getByTin($data['tin']);
        if ($existing) {
            $this->update($existing['id'], $data);
        } else {
            $this->insert($data);
        }
    }

    public function delinkParticipant(string $tin, string $delinkedAt): void
    {
        $this->where('tin', $tin)->set([
            'status'      => 'delinked',
            'delinked_at' => $delinkedAt,
        ])->update();
    }
}
