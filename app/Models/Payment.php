<?php

namespace App\Models;

use App\Models\SuperAdmin\Subscription;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends BaseModel
{
    protected $table = 'payments';

    protected $fillable = [
        'company_id', 'subscription_id', 'stripe_invoice_id',
        'transaction_id', 'amount', 'currency', 'status',
        'payment_method', 'description', 'metadata', 'paid_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'paid_at'  => 'datetime',
        'amount'   => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
