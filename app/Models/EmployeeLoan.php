<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeLoan extends Model
{
    use HasFactory;

    protected $table = 'employee_loans';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'issued_date'          => 'date',
            'first_installment_date' => 'date',
            'approved_at'          => 'datetime',
            'principal_amount'     => 'decimal:2',
            'approved_amount'      => 'decimal:2',
            'installment_amount'   => 'decimal:2',
            'interest_rate_percent' => 'decimal:2',
        ];
    }

    // ─── Status constants ───────────────────────────────────────────────────
    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ACTIVE   = 'active';   // legacy: loan already disbursed
    const STATUS_CLOSED   = 'closed';

    public static array $statusLabels = [
        'pending'  => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'active'   => 'Active',
        'closed'   => 'Closed',
    ];

    public static array $loanTypes = [
        'personal'   => 'Personal Loan',
        'education'  => 'Education Loan',
        'medical'    => 'Medical Loan',
        'vehicle'    => 'Vehicle Loan',
        'home'       => 'Home Loan',
        'emergency'  => 'Emergency Loan',
        'other'      => 'Other',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(LoanInstallment::class, 'employee_loan_id');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_APPROVED]);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    /**
     * Calculate how much of the loan has been repaid via paid installments.
     */
    public function getPaidAmountAttribute(): float
    {
        return (float) $this->installments()->where('status', 'paid')->sum('paid_amount');
    }

    /**
     * Remaining balance = approved/principal amount – paid installments.
     */
    public function getRemainingBalanceAttribute(): float
    {
        $totalAmount = (float) ($this->approved_amount ?? $this->principal_amount);
        return max(0, $totalAmount - $this->paid_amount);
    }

    /**
     * Generate a unique loan reference code.
     */
    public static function generateReference(): string
    {
        do {
            $ref = 'LN-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
        } while (static::where('loan_reference', $ref)->exists());

        return $ref;
    }
}
