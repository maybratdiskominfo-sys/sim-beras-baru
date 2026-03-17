<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserObserver
{
    /**
     * Hapus foto lama jika foto diupdate
     */
    public function updated(User $user): void
    {
        if ($user->isDirty('avatar_url')) {
            $oldPhoto = $user->getOriginal('avatar_url');
            
            if ($oldPhoto && Storage::disk('public')->exists($oldPhoto)) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }
    }

    /**
     * Hapus foto jika user dihapus
     */
    public function deleted(User $user): void
    {
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }
    }
}