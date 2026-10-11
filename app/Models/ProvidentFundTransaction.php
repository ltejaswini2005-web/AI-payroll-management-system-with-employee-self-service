<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProvidentFundTransaction extends Model
{
    use HasFactory;

    protected $table = 'provident_fund_transactions';

    protected $guarded = [];

    protected $casts = [
        'transaction_date' => 'date',
        'employee_contribution' => 'decimal:2',
        'employer_contribution' => 'decimal:2',
        'withdrawal_amount' => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }
}
