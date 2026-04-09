<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends BaseModel
{
    use HasTenant, SoftDeletes;

    protected $table = 'documents';

    protected $fillable = [
        'company_id', 'user_id', 'name', 'original_name',
        'mime_type', 'disk', 'path', 'size',
        'documentable_type', 'documentable_id', 'is_private',
    ];

    protected $casts = ['is_private' => 'boolean'];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getDownloadUrlAttribute(): string
    {
        if ($this->disk === 's3') {
            return Storage::disk('s3')->temporaryUrl($this->path, now()->addMinutes(5));
        }
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getFormattedSizeAttribute(): string
    {
        return format_bytes($this->size ?? 0);
    }
}
