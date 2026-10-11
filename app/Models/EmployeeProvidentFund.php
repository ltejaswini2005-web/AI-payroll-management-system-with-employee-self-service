<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeProvidentFund extends Model
{
    use HasFactory;

    protected $table = 'employee_provident_funds';

    protected $guarded = [];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ProvidentFundTransaction::class, 'employee_id', 'employee_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(ProvidentFundClaim::class, 'employee_id', 'employee_id');
    }
}
