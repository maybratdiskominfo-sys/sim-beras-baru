<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Dotswan\MapPicker\Fields\Map;

class DepartmentResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Department::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Manajemen Kantor';
    protected static ?string $modelLabel = 'Kantor';
    protected static ?string $pluralModelLabel = 'Daftar Kantor';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 1;
    protected static bool $isScopedToTenant = false;

    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user && $user->hasRole('super_admin');
    }

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Section::make('Informasi Dasar')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->label('Nama Dinas')
                                    ->placeholder('Contoh: Dinas Komunikasi dan Informatika')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => 
                                        $set('code', Str::upper(Str::slug($state)))),
                                
                                TextInput::make('code')
                                    ->required()
                                    ->label('Kode Dinas')
                                    ->unique(ignoreRecord: true)
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                    ->placeholder('Contoh: DISKOMINFO'),
                                
                                Textarea::make('alamat_kantor')
                                    ->label('Alamat Lengkap Kantor')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Pejabat & Penanggung Jawab')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Fieldset::make('Pejabat Utama (Kepala Dinas)')
                                            ->schema([
                                                TextInput::make('nama_kadin')->label('Nama Lengkap & Gelar'),
                                                TextInput::make('nip_kadin')->label('NIP Kepala Dinas'),
                                            ]),

                                        Fieldset::make('Pejabat Teknis (Admin OPD)')
                                            ->schema([
                                                TextInput::make('nama_petugas')->label('Nama Admin'),
                                                TextInput::make('nip_petugas')->label('NIP Admin'),
                                            ]),
                                    ]),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Section::make('Titik Lokasi & Radius')
                            ->description('Tentukan lokasi kantor di peta untuk Geofencing.')
                            ->schema([
                                Map::make('location')
                                    ->label('Pilih Lokasi Kantor')
                                    ->columnSpanFull()
                                    ->afterStateHydrated(function ($state, $record, $set) {
                                        if ($record) {
                                            $set('location', [
                                                'lat' => (float) $record->latitude,
                                                'lng' => (float) $record->longitude,
                                            ]);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, $set) {
                                        $set('latitude', $state['lat']);
                                        $set('longitude', $state['lng']);
                                    })
                                    ->showMarker()
                                    ->draggable()
                                    ->zoom(15),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('latitude')
                                            ->label('Latitude')
                                            // ->readonly()
                                            ->required(),
                                        TextInput::make('longitude')
                                            ->label('Longitude')
                                            // ->readonly()
                                            ->required(),
                                    ]),

                                TextInput::make('radius_meter')
                                    ->label('Radius Aman')
                                    ->numeric()
                                    ->default(100)
                                    ->suffix('Meter')
                                    ->helperText('Jarak maksimal pegawai bisa absen.')
                                    ->required(),
                            ]),

                        Section::make('Visual')
                            ->schema([
                                FileUpload::make('logo_kiri')
                                    ->label('Logo Instansi')
                                    ->image()
                                    ->directory('logos')
                                    ->visibility('public'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')->label('No.')->rowIndex(),

                ImageColumn::make('logo_kiri')->label('Logo')->circular()->disk('public'),

                TextColumn::make('name')
                    ->label('Nama Kantor')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('info'),

                TextColumn::make('location')
                    ->label('Geofence')
                    ->state(fn ($record) => "{$record->latitude}, {$record->longitude}")
                    ->description(fn ($record) => "Radius: {$record->radius_meter}m")
                    ->icon('heroicon-m-map-pin'),

                TextColumn::make('employees_count')
                    ->label('Total SDM')
                    ->counts('employees')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()->slideOver()->modalWidth('7xl'),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
        ];
    }
}