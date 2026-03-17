<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Mengambil ID Tenant yang sedang aktif di session Filament
        $tenantId = Filament::getTenant()?->id;

        // Pastikan department_id terisi otomatis sesuai Tenant yang sedang dibuka
        $data['department_id'] = $tenantId;
        
        // Simpan ID Penulis
        $data['user_id'] = Auth::id();

        // Ambil kategori dari bidang pegawai
        $data['category'] = Auth::user()->employee->bidang ?? 'Umum';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}