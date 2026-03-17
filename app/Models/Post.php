<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal.
     * Pastikan department_id sudah ada di database agar tidak error.
     */
    protected $fillable = [
        'user_id', 
        'department_id',
        'title', 
        'slug', 
        'category', 
        'thumbnail', 
        'content', 
        'status'
    ];

    /**
     * Relasi ke model User (Penulis berita).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke model Department (Tenant/Pemilik berita).
     * Relasi ini WAJIB ada agar Filament bisa menampilkan data di tabel PostResource.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}