<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceTransmissionModel extends Model
{
    protected $table      = 'tbl_invoice_transmissions';
    protected $primaryKey = 'id';

    protected $allowedFields = ['invoice_id', 'event', 'message', 'level'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = null;

    // Event types
    const EVENT_CREATED     = 0;
    const EVENT_SUBMITTED   = 1;
    const EVENT_AP_RECEIVED = 2;
    const EVENT_SMP_LOOKUP  = 3;
    const EVENT_DELIVERED   = 4;
    const EVENT_FAILED      = 5;

    // Level
    const LEVEL_INFO    = 0;
    const LEVEL_SUCCESS = 1;
    const LEVEL_ERROR   = 2;

    public static $eventLabels = [
        self::EVENT_CREATED     => 'Created',
        self::EVENT_SUBMITTED   => 'Submitted to AP',
        self::EVENT_AP_RECEIVED => 'AP Received',
        self::EVENT_SMP_LOOKUP  => 'SMP Lookup',
        self::EVENT_DELIVERED   => 'Delivered',
        self::EVENT_FAILED      => 'Failed',
    ];

    public static $levelColors = [
        self::LEVEL_INFO    => 'blue',
        self::LEVEL_SUCCESS => 'green',
        self::LEVEL_ERROR   => 'red',
    ];

    public function addEvent(int $invoiceId, int $event, string $message = '', int $level = self::LEVEL_INFO): void
    {
        $this->insert([
            'invoice_id' => $invoiceId,
            'event'      => $event,
            'message'    => $message,
            'level'      => $level,
        ]);
    }

    public function getByInvoice(int $invoiceId): array
    {
        return $this->where('invoice_id', $invoiceId)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }
}
