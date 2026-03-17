<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class EditProfile extends BaseEditProfile
{
    /**
     * MENGARAHKAN KE DASHBOARD SETELAH SAVE
     */
    protected function getRedirectUrl(): ?string
    {
        return '/admin'; 
    }

    /**
     * Mengatur lebar konten agar tetap seimbang
     */
    public function getMaxContentWidth(): string
    {
        return '4xl'; 
    }

    protected function getFormActionsAreSticky(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section Foto Profil
                Section::make('Foto Profil')
                    ->description('Gunakan foto wajah formal terbaru.')
                    ->icon('heroicon-m-camera')
                    ->schema([
                FileUpload::make('avatar_url')
                    ->label('')
                    ->image()
                    ->avatar()
                    ->imageEditor()
            
                    // LOGIKA FOLDER DINAMIS
                    ->directory(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        
                        // Ambil nama departemen, bersihkan karakter aneh dengan Slug
                        // Jika tidak ada departemen, masukkan ke folder 'umum'
                        $folderDepartemen = $user->department ? Str::slug($user->department->name) : 'umum';

                        return "avatars/{$folderDepartemen}";
                    })
                    // LOGIKA PENAMAAN FILE CUSTOM
                    ->getUploadedFileNameForStorageUsing(function ($get) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        
                        // Gunakan nama lengkap dari relasi employee jika ada, jika tidak gunakan field 'name'
                        $nama = $user->employee?->nama_lengkap ?? $get('name') ?? 'user';
                        
                        $slug = Str::slug($nama);
                        
                        return (string) $slug . '.webp'; 
                    })
                    ->alignCenter(),
                    ])->collapsible(),

                // Informasi Dasar
                Section::make('Informasi Profil')
                    ->description('Perbarui informasi identitas akun Anda.')
                    ->icon('heroicon-m-user-circle')
                    ->schema([
                        $this->getNameFormComponent()
                            ->label('Nama Pengguna'), // Biasanya sinkron dengan nama pegawai
                        
                        Grid::make(2)
                            ->schema([
                                $this->getEmailFormComponent()
                                    ->columnSpanFull(),
                            ]),
                    ])->collapsible(),

                // Keamanan/Password
                Section::make('Keamanan Akun')
                    ->description('Pastikan akun Anda menggunakan password yang kuat.')
                    ->icon('heroicon-m-shield-check')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                $this->getPasswordFormComponent()
                                    ->columnSpanFull(),
                                $this->getPasswordConfirmationFormComponent()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(), 
            ]);
    }
}