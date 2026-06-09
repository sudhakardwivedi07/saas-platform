<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends BaseModel
{
    use SoftDeletes;

    protected $table = 'reports';

    protected $fillable = [
        'user_id',
        'company_id',
        'is_firm_report',
        'report_name',
        'upload_date',
        'total_file_size',
        'status',
        'publish_status',
        'one_page_insight',
        'seven_page_insight',
        'insight_json_data',
        'version',
        'parent_report_id',
    ];

    protected $casts = [
        'upload_date'       => 'date',
        'total_file_size'   => 'integer',
        'insight_json_data' => 'array',
        'is_firm_report'    => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReportAttachment::class, 'report_id');
    }

    // Original report this version was derived from (null = this IS the original)
    public function parentReport(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'parent_report_id');
    }

    // All version-copies spawned from this report
    public function versionCopies(): HasMany
    {
        return $this->hasMany(Report::class, 'parent_report_id');
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

    public function isDraft(): bool
    {
        return ($this->publish_status ?? 'draft') === 'draft';
    }

    public function isPublished(): bool
    {
        return ($this->publish_status ?? 'draft') === 'published';
    }

    public function getPublishStatusLabelAttribute(): string
    {
        return match ($this->publish_status ?? 'draft') {
            'published' => 'Published',
            default     => 'Draft',
        };
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

    /**
     * Monthly insight usage for limit UI, scoped like the Insights list:
     * reports for company_id = $selectedCompanyId, plus legacy rows with null company_id
     * only when the user’s home company matches $selectedCompanyId.
     */
    public static function countMonthlyUsageForUserCompany(int $userId, int $selectedCompanyId, int $userHomeCompanyId): int
    {
        return static::withTrashed()
            ->where('user_id', $userId)
            ->whereNull('parent_report_id')
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->where(function ($q) use ($selectedCompanyId, $userHomeCompanyId) {
                $q->where('company_id', $selectedCompanyId);
                if ($userHomeCompanyId && (int) $selectedCompanyId === (int) $userHomeCompanyId) {
                    $q->orWhereNull('company_id');
                }
            })
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNull('deleted_at')
                        ->whereNotIn('status', ['Failed', 'Deleted']);
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('deleted_at')
                        ->whereRaw('LOWER(TRIM(status)) = ?', ['analyzed']);
                });
            })
            ->get(['report_name', 'company_id'])
            ->unique(fn($r) => $r->report_name . '||' . ($r->company_id ?? 0))
            ->count();
    }
}
