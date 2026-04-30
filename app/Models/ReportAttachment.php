<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportAttachment extends BaseModel
{
    protected $table = 'report_attachments';

    protected $fillable = [
        'report_id',
        'attachment',
        'file_size',
        'file_type',
        'original_filename',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'report_id');
    }
}
