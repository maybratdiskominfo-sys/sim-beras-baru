<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    // Pastikan 'tenant_id' ada di sini!
    protected $fillable = ['tenant_id', 'date', 'description'];

    protected $casts = [
        'date' => 'date',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'tenant_id');
    }
}