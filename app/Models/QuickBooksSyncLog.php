<?php

namespace App\Models;

class QuickBooksSyncLog extends BaseModel
{
    public $timestamps = false;

    protected $table = 'quickbooks_sync_logs';

    protected $fillable = [
        'connection_id',
        'sync_type',
        'status',
        'records_synced',
        'error_message',
        'started_at',
        'completed_at',
        'created_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'created_at'   => 'datetime',
    ];
}
