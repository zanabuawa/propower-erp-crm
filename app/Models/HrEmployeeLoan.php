<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeLoan extends Model
{
    use BelongsToCompany;

    protected $table = 'hr_employee_loans';

    protected $fillable = [
        'company_id', 'employee_id', 'amount', 'balance',
        'installment_amount', 'reason', 'loan_date', 'status'
    ];

    protected $casts = [
        'amount'             => 'decimal:2',
        'balance'            => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'loan_date'          => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    /** Register a payment to the loan */
    public function registerPayment(float $amount): void
    {
        $this->balance -= $amount;
        if ($this->balance <= 0) {
            $this->balance = 0;
            $this->status = 'paid';
        }
        $this->save();
    }
}
