<?php

namespace App\Models;

class EventLog extends BaseModel
{
    public $timestamps = false;

    protected $table = 'event_logs';

    protected $fillable = [
        'company_id',
        'user_id',
        'event_type',
        'description',
        'metadata',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Convenience method to record an audit event.
     */
    public static function record(string $type, string $description, array $meta = []): void
    {
        self::create([
            'company_id'  => company()?->id,
            'user_id'     => auth()->id(),
            'event_type'  => $type,
            'description' => $description,
            'metadata'    => $meta,
            'ip_address'  => request()->ip(),
            'created_at'  => now(),
        ]);
    }
}
