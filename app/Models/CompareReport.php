<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompareReport extends BaseModel
{
    use SoftDeletes;

    protected $table = 'compare_reports';

    protected $fillable = [
        'user_id',
        'company_id',
        'report_id_a',
        'report_id_b',
        'period_a',
        'period_b',
        'kpi_data_a',
        'kpi_data_b',
        'comparison_result',
        'status',
    ];

    protected $casts = [
        'kpi_data_a'         => 'array',
        'kpi_data_b'         => 'array',
        'comparison_result'  => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function reportA(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'report_id_a');
    }

    public function reportB(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'report_id_b');
    }

    public function getEncryptedIdAttribute(): string
    {
        $key = hash('sha256', 'worksuite');
        $iv  = substr(hash('sha256', 'froiden'), 0, 16);
        return base64_encode(openssl_encrypt((string) $this->id, 'AES-256-CBC', $key, 0, $iv));
    }

    public static function decryptId(string $encryptedId): ?int
    {
        $key = hash('sha256', 'worksuite');
        $iv  = substr(hash('sha256', 'froiden'), 0, 16);
        $id  = openssl_decrypt(base64_decode($encryptedId), 'AES-256-CBC', $key, 0, $iv);
        return ($id && is_numeric($id)) ? (int) $id : null;
    }
}
