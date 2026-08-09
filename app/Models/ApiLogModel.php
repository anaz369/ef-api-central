<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiLogModel extends Model
{
    protected $table      = 'tbl_api_logs';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'participant_id', 'credential_id', 'endpoint', 'method',
        'request_body', 'response_body', 'response_code',
        'environment', 'status', 'ip_address',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = null;

    public function getList(array $filters = []): array
    {
        $builder = $this->db->table('tbl_api_logs l')
            ->select('l.*, p.name AS participant_name')
            ->join('tbl_participants p', 'p.id = l.participant_id', 'left')
            ->orderBy('l.created_at', 'DESC');

        if (!empty($filters['participant_id'])) {
            $builder->where('l.participant_id', (int) $filters['participant_id']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $builder->where('l.status', (int) $filters['status']);
        }
        if (isset($filters['environment']) && $filters['environment'] !== '') {
            $builder->where('l.environment', (int) $filters['environment']);
        }

        return $builder->limit(1000)->get()->getResultArray();
    }

    public function record(array $data): void
    {
        $this->insert($data);
    }
}
