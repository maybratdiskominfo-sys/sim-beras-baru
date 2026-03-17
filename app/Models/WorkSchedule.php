<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkSchedule extends Model
{
    protected $fillable = [
        'tenant_id',
        'day',
        'start_time',
        'end_time',
        'is_active',
    ];

    // Relasi ke Department (Tenant)
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'tenant_id');
    }
}