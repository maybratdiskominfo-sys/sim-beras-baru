<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Izinkan semua user yang sudah login untuk melihat daftar berita.
     * Filter departemen sudah ditangani secara otomatis oleh Filament Tenancy.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Izinkan user melihat detail berita tertentu.
     */
    public function view(User $user, Post $post): bool
    {
        return true;
    }

    /**
     * Izinkan semua user yang login untuk membuat berita.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * HANYA pemilik postingan atau Super Admin yang boleh mengubah berita.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->hasRole('super_admin');
    }

    /**
     * HANYA pemilik postingan atau Super Admin yang boleh menghapus berita.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->hasRole('super_admin');
    }

    /**
     * Kebijakan untuk memulihkan data (jika menggunakan SoftDeletes).
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->hasRole('super_admin');
    }

    /**
     * Kebijakan untuk menghapus permanen.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->hasRole('super_admin');
    }
}