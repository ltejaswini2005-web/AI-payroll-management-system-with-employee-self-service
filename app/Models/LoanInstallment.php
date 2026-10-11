<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanInstallment extends Model
{
    use HasFactory;

    protected $table = 'loan_installments';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'due_date'  => 'date',
            'paid_date' => 'date',
            'amount'      => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function employeeLoan(): BelongsTo
    {
        return $this->belongsTo(EmployeeLoan::class, 'employee_loan_id');
    }
}
