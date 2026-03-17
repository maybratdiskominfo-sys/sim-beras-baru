<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Manajemen User';
    protected static ?string $pluralModelLabel = 'Manajemen User';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 2;

    /**
     * Menonaktifkan Scoping Tenant agar SuperAdmin bisa melihat
     * semua user dari semua bidang/dinas tanpa filter otomatis.
     */
    protected static bool $isScopedToTenant = false;
    
    /**
     * Membatasi akses menu hanya untuk Super Admin secara global.
     */
    public static function canAccess(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->hasRole(['super_admin']);
    }

    /**
     * Definisi Izin Shield untuk Resource ini.
     */
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identitas Pengguna')
                    ->description('Kelola informasi dasar dan akun login pengguna.')
                    ->schema([

                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->revealable()
                            ->helperText(fn (string $context): string => $context === 'edit' ? 'Kosongkan jika tidak ingin mengubah password.' : ''),
                    ])->columns(2),

                Section::make('Otoritas & Penugasan')
                    ->description('Tentukan hak akses dan penempatan bidang kerja untuk sistem Tenancy.')
                    ->schema([
                        // Relasi ke Roles (Filament Shield)
                        Select::make('roles')
                            ->label('Hak Akses (Role)')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required(),

                        // Relasi ke Departments (Tenancy) - Mengisi tabel pivot
                        Select::make('department_id')
                            ->label('Akses Dinas (Tenants)')
                            ->relationship('department', 'name')
                            // ->multiple()
                            ->preload()
                            ->searchable()
                            ->placeholder('Pilih Bidang/Dinas')
                            // TAMBAHKAN LOGIKA INI:
                            // Mengambil ID pertama dari pilihan multiple untuk disimpan ke kolom department_id user
                            ->dehydrated(fn ($state) => filled($state))
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Jika Anda ingin kolom department_id utama selalu sinkron dengan pilihan pertama
                                if (is_array($state) && count($state) > 0) {
                                    // Kita akan menangani penyimpanan manual di level Page agar lebih aman
                                }
                            })
                            ->helperText('User akan memiliki akses dashboard pada bidang yang dipilih.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                ImageColumn::make('avatar_url')
                    ->label('Foto')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record): string => $record->email),

                // Toggle Aktivasi: Sekarang hanya mengubah status tanpa memicu email
                ToggleColumn::make('is_active')
                    ->label('Status Akses')
                    ->onColor('success')
                    ->offColor('danger')
                    ->tooltip('Geser untuk memberikan atau mencabut akses dashboard user'),

                TextColumn::make('department.code')
                    ->label('Dinas')
                    ->badge()
                    ->color('info')
                    ->separator(', ')
                    ->limitList(2),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('created_at')
                    ->label('Tgl Daftar')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Filter Kantor')
                    ->relationship('department', 'name')
                    ->visible(fn () => $user->hasRole('super_admin')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Akun')
                    ->trueLabel('Aktif')
                    ->falseLabel('Non-Aktif')
                    ->placeholder('Semua Status'),
            ])

            ->actions([
                // Tables\Actions\ViewAction::make()
                // ->iconButton()// Mengubah tombol menjadi hanya icon bulat
                // ->tooltip('Lihat Detail'), // Menambahkan panduan saat kursor menempel

                Tables\Actions\EditAction::make()
                ->iconButton()
                ->slideOver()
                ->modalWidth('2xl') // Mengatur lebar pop-up agar pas
                ->modalHeading('Ubah Data Pegawai') // Judul yang lebih spesifik & profesional
                ->modalIcon('heroicon-o-pencil-square')
                ->tooltip('Ubah Data'),

                Tables\Actions\DeleteAction::make()
                ->iconButton()// Mengubah tombol menjadi hanya icon bulat
                ->tooltip('Hapus Data') // Menambahkan panduan saat kursor menempel
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Berhasil')
                        ->body('Data pegawai telah di hapus dari sistem.')
                ),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
