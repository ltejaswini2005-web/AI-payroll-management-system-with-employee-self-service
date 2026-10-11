<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceEnrollment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'enrolled_at' => 'datetime',
            'face_descriptor' => 'json',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function enrolledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    public function getDescriptorAsArray(): array
    {
        $descriptor = $this->face_descriptor;
        return is_array($descriptor) ? $descriptor : json_decode((string) $descriptor, true) ?? [];
    }
}
