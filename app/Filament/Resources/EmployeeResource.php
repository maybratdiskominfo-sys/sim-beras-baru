<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Notifications\Notification;

class EmployeeResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Data Pegawai';
    protected static ?string $modelLabel = 'Pegawai';
    protected static ?string $pluralModelLabel = 'Data Pegawai';
    protected static ?string $navigationGroup = 'Data Operasional';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('user');
        
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jika super_admin, lihat semua. Jika Admin OPD, hanya lihat departemennya sendiri.
        if ($user?->hasRole('super_admin')) {
            return $query->withoutGlobalScopes();
        }

        return $query->where('department_id', $user->department_id);
    }

    public static function isScopedToTenant(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return !($user?->hasRole('super_admin'));
    }

    public static function getPermissionPrefixes(): array
    {
        return 
        [
            'view', 
            'view_any', 
            'create', 
            'update', 
            'delete', 
            'delete_any'
        ];
    }

    public static function form(Form $form): Form
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $form
            ->schema([
                Section::make('Identitas Pegawai')
                    ->description('Data Master pegawai. Penempatan Kantor akan otomatis terkunci sesuai login Anda jika bukan Super Admin.')
                    ->schema([
                        TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('email@kantor.go.id'),

                        TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->numeric()
                            ->length(18)
                            ->placeholder('Contoh: 198801012015011001')
                            ->validationMessages([
                                'length' => 'NIP harus tepat 18 karakter.',
                                'unique' => 'NIP ini sudah terdaftar dalam sistem.',
                            ]),

                        TextInput::make('nomor_hp')
                            ->label('Nomor Telepon/WA')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('081234567890')
                            ->prefix('+62'),

                        // OTOMATISASI DEPARTMENT ID
                        Select::make('department_id')
                            ->label('Penempatan Dinas | Kantor')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default($user->department_id)
                            ->disabled(! $user->hasRole('super_admin'))
                            ->dehydrated(true), // Penting: agar data tetap dikirim saat save

                        Select::make('status_pegawai')
                            ->label('Status Kepegawaian')
                            ->options([
                                'PNS' => 'PNS',
                                'PPPK' => 'PPPK',
                                'Honor' => 'Honor',
                            ])
                            ->required(),

                        Select::make('golongan')
                            ->label('Golongan | Pangkat')
                            ->options(Employee::getGolonganOptions())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('position')
                            ->label('Jabatan')
                            ->options(Employee::getPositionOptions())
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Section::make('Pengaturan Logistik')
                    ->schema([
                        TextInput::make('jatah_kg')
                            ->label('Jatah Beras')
                            ->numeric()
                            ->suffix('Kg')
                            ->default(10)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Status Pegawai Aktif')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(false)
                            // Kunci akses: Hanya Super Admin yang bisa mengubah status keaktifan
                            ->disabled(!$user->hasRole('super_admin'))
                            // WAJIB: Agar nilai 'false' atau status saat ini tetap terkirim ke database
                            ->dehydrated(true) 
                            ->helperText($user->hasRole('super_admin') 
                                ? 'Gunakan ini untuk mengaktifkan atau menonaktifkan akses pegawai.' 
                                : 'Menunggu verifikasi Super Admin untuk aktif.'
                            )
                            
                            ->hint(!$user->hasRole('super_admin') ? 'Terkunci' : null)
                            ->hintIcon('heroicon-m-lock-closed')
                            ->hintColor('warning')

                            ->afterStateUpdated(function ($state) {
                                // Logic tambahan jika ingin ada notifikasi instan
                            }),

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

                ImageColumn::make('user.avatar_url')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nama_lengkap')
                    ->label('Nama Pegawai')
                    ->copyable()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nip')
                    ->label('NIP')
                    ->icon('heroicon-m-identification')
                    ->iconColor('gray')
                    ->fontFamily('mono')
                    ->copyable()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('department.code')
                    ->label('Instansi Dinas')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('jatah_kg')
                    ->label('Jatah')
                    ->numeric(decimalPlaces: 1)
                    ->suffix(' Kg')
                    ->badge()
                    ->color('success')
                    ->alignRight(),

                // KOLOM STATUS OTOMATIS
                TextColumn::make('is_active')
                    ->label('Status Verifikasi')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Proses Verifikasi')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-m-check-badge' : 'heroicon-m-clock')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Filter Kantor')
                    ->relationship('department', 'name')
                    ->visible(fn () => $user->hasRole('super_admin')),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Hanya Pegawai Aktif'),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make()
                // ->iconButton()
                // ->slideOver()
                // ->tooltip('Lihat Detail'),
                
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->modalHeading('Ubah Data Pegawai')
                    ->tooltip('Ubah Data')
                    ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Berhasil')
                        ->body('Data pegawai telah di update ke sistem.')
                    ),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Hapus Data')
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

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            // 'create' => Pages\CreateEmployee::route('/create'),
        ];
    }
}