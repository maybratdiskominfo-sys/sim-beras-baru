<?php

namespace App\Observers;

use App\Models\Department;
use Illuminate\Support\Facades\Storage;

class DepartmentObserver
{
    /**
     * Menghapus logo lama jika logo diganti (Update)
     */
    public function updated(Department $department): void
    {
        if ($department->isDirty('logo_kiri')) {
            $oldLogo = $department->getOriginal('logo_kiri');
            
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
        }
    }

    /**
     * Menghapus logo saat kantor dihapus (Delete)
     */
    public function deleted(Department $department): void
    {
        if ($department->logo_kiri && Storage::disk('public')->exists($department->logo_kiri)) {
            Storage::disk('public')->delete($department->logo_kiri);
        }
    }
}