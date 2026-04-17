<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLoan extends Model
{
    protected $fillable = [
        'company_id', 'fixed_asset_id', 'loaned_to_user_id', 'loaned_to_name',
        'created_by', 'folio', 'loan_date', 'expected_return_date', 'actual_return_date',
        'condition_on_loan', 'condition_on_return', 'status',
        'purpose', 'notes', 'return_notes', 'returned_by',
    ];

    protected $casts = [
        'loan_date'            => 'date',
        'expected_return_date' => 'date',
        'actual_return_date'   => 'date',
    ];

    const STATUSES = [
        'active'   => 'Prestado',
        'returned' => 'Devuelto',
        'lost'     => 'Pérdida',
        'damaged'  => 'Dañado',
    ];

    const CONDITIONS = [
        'good'    => 'Bueno',
        'fair'    => 'Regular',
        'damaged' => 'Dañado',
        'lost'    => 'Pérdida total',
    ];

    const STATUS_COLORS = [
        'active'   => 'blue',
        'returned' => 'green',
        'lost'     => 'red',
        'damaged'  => 'yellow',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function loanedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loaned_to_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getRecipientNameAttribute(): string
    {
        return $this->loanedToUser?->name ?? $this->loaned_to_name ?? '—';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active'
            && $this->expected_return_date
            && $this->expected_return_date->isPast();
    }

    public static function generateFolio(int $companyId): string
    {
        $count = self::where('company_id', $companyId)->count() + 1;
        return 'PREST-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
