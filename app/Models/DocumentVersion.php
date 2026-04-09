<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends BaseModel
{
    public $timestamps = false;
    protected $table   = 'document_versions';

    protected $fillable = [
        'document_id', 'version_number', 'path', 'size', 'uploaded_by', 'notes',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
