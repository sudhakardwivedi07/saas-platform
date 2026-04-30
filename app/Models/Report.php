<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends BaseModel
{
    use SoftDeletes;

    protected $table = 'reports';

    protected $fillable = [
        'user_id',
        'report_name',
        'upload_date',
        'total_file_size',
        'status',
        'one_page_insight',
        'seven_page_insight',
        'insight_json_data',
    ];

    protected $casts = [
        'upload_date'       => 'date',
        'total_file_size'   => 'integer',
        'insight_json_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReportAttachment::class, 'report_id');
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->total_file_size;
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' bytes';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match (strtolower($this->status)) {
            'uploading'  => 'info',
            'analyzing'  => 'primary',
            'analyzed'   => 'success',
            'failed'     => 'danger',
            default      => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match (strtolower($this->status)) {
            'uploading'  => 'Processing',
            'analyzing'  => 'Analyzing',
            'analyzed'   => 'Ready',
            'failed'     => 'Failed',
            default      => ucfirst($this->status),
        };
    }

    public function isReady(): bool
    {
        return strtolower($this->status) === 'analyzed' && !empty($this->insight_json_data);
    }

    public function isProcessing(): bool
    {
        return in_array(strtolower($this->status), ['uploading', 'analyzing']);
    }

    public function getEncryptedIdAttribute(): string
    {
        $key = hash('sha256', 'worksuite');
        $iv  = substr(hash('sha256', 'froiden'), 0, 16);
        return base64_encode(openssl_encrypt((string) $this->id, 'AES-256-CBC', $key, 0, $iv));
    }

    public static function decryptId(string $encryptedId): ?int
    {
        try {
            $key       = hash('sha256', 'worksuite');
            $iv        = substr(hash('sha256', 'froiden'), 0, 16);
            $decrypted = openssl_decrypt(base64_decode($encryptedId), 'AES-256-CBC', $key, 0, $iv);
            return $decrypted !== false && is_numeric($decrypted) ? (int) $decrypted : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
