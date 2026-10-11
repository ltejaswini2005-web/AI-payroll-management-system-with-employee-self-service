<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProvidentFundClaim extends Model
{
    use HasFactory;

    protected $table = 'provident_fund_claims';

    protected $guarded = [];

    protected $casts = [
        'submission_date' => 'date',
        'approved_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
