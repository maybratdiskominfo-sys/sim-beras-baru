<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Holiday;
use App\Models\Employee;
use Filament\Forms\Form;
use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\WorkSchedule;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Dotswan\MapPicker\Fields\Map;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AttendanceResource\Pages;
use Filament\Notifications\Notification;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Data Operasional';
    protected static ?string $navigationLabel = 'Presensi Pegawai';
    protected static ?int $navigationSort = 2;
    
    /** * AKTIVASI TENANCY:
     * Menjamin data Attendance di-scope otomatis berdasarkan department_id (ID OPD)
     */
    protected static bool $isScopedToTenant = true;
    protected static ?string $tenantOwnershipRelationshipName = 'department';

    /**
     * FILTER QUERY GLOBAL:
     * Memastikan hanya presensi dari pegawai yang is_active = true yang muncul di tabel
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('employee', function (Builder $query) {
                $query->where('is_active', true);
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Identitas & Waktu')
                            ->description('Pilih pegawai aktif di departemen Anda.')
                            ->icon('heroicon-m-user-circle')
                            ->schema([
                                Forms\Components\Select::make('employee_id')
                                    ->label('Nama Pegawai')
                                    ->relationship('employee', 'nama_lengkap', function (Builder $query) {
                                        /**
                                         * LOCK TENANT & STATUS:
                                         * Memaksa dropdown hanya menampilkan pegawai dari dinas admin
                                         * yang statusnya masih aktif (is_active = true).
                                         */
                                        return $query
                                            ->where('department_id', filament()->getTenant()->id)
                                            ->where('is_active', true);
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $emp = Employee::with('department')->find($get('employee_id'));
                                        if ($emp && $emp->department) {
                                            // Set ID Departemen secara otomatis sesuai pegawai
                                            $set('department_id', $emp->department_id);
                                            
                                            // Auto-focus peta ke lokasi kantor dinas tersebut
                                            $set('location', [
                                                'lat' => (float) $emp->department->latitude,
                                                'lng' => (float) $emp->department->longitude,
                                            ]);
                                            $set('location_lat_long', "{$emp->department->latitude},{$emp->department->longitude}");
                                        }
                                        static::autoCalculateStatus($set, $get);
                                    }),

                                Forms\Components\Hidden::make('department_id'),

                                Forms\Components\Select::make('status')
                                    ->label('Status Kehadiran')
                                    ->options([
                                        'Hadir' => 'Hadir',
                                        'Terlambat' => 'Terlambat',
                                        'Izin' => 'Izin',
                                        'Sakit' => 'Sakit',
                                        'Dinas Luar' => 'Dinas Luar',
                                        'Alpa' => 'Alpa',
                                        'Libur' => 'Libur',
                                    ])
                                    ->required()
                                    ->live(),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DatePicker::make('date')
                                            ->label('Tanggal')
                                            ->default(now())
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn (Set $set, Get $get) => static::autoCalculateStatus($set, $get)),

                                        Forms\Components\TimePicker::make('check_in')
                                            ->label('Jam Masuk')
                                            ->default(now()->format('H:i:s'))
                                            ->required(),
                                    ]),
                                
                                Forms\Components\Textarea::make('notes')
                                    ->label('Keterangan untuk Laporan')
                                    ->placeholder('Misal: Izin sakit dengan surat dokter / Alpa tanpa kabar.')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Validasi Lokasi')
                            ->icon('heroicon-m-map-pin')
                            ->hidden(fn (Get $get) => !in_array($get('status'), ['Hadir', 'Terlambat']))
                            ->schema([
                                Map::make('location')
                                    ->columnSpanFull()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if ($state) {
                                            $set('location_lat_long', "{$state['lat']},{$state['lng']}");
                                        }
                                    })
                                    ->draggable(true) 
                                    ->zoom(17),

                                Forms\Components\Placeholder::make('info_kantor')
                                    ->label('Keterangan')
                                    ->content('Titik koordinat otomatis diarahkan ke lokasi Kantor Dinas pegawai.'),
                                
                                Forms\Components\Hidden::make('distance_from_office')->default(0),
                                Forms\Components\Hidden::make('location_lat_long'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    /**
     * LOGIKA HITUNG STATUS OTOMATIS
     */
    public static function autoCalculateStatus(Set $set, Get $get): void
    {
        $date = $get('date');
        $checkIn = $get('check_in');
        $empId = $get('employee_id');

        if (!$date || !$empId) return;

        $emp = Employee::find($empId);
        if (!$emp) return;

        $tenantId = $emp->department_id;
        $carbonDate = Carbon::parse($date);
        
        // 1. Cek Libur
        $holiday = Holiday::where('tenant_id', $tenantId)->whereDate('date', $carbonDate)->first();
        if ($holiday) {
            $set('status', 'Libur');
            return;
        }

        // 2. Cek Jadwal
        $schedule = WorkSchedule::where('tenant_id', $tenantId)
            ->where('day', $carbonDate->format('l'))
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            $set('status', 'Libur');
            return;
        }

        // 3. Cek Jam Masuk
        if ($checkIn) {
            $isLate = Carbon::parse($checkIn)->format('H:i:s') > $schedule->start_time;
            $set('status', $isLate ? 'Terlambat' : 'Hadir');
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.nama_lengkap')
                    ->label('Nama Pegawai')
                    ->description(fn($record) => "NIP: " . $record->employee->nip)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Terlambat', 'Alpa' => 'danger',
                        'Izin', 'Sakit', 'Dinas Luar' => 'warning',
                        'Libur' => 'info',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('check_in')
                    ->label('Jam')
                    ->time('H:i'),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Hadir' => 'Hadir', 
                        'Terlambat' => 'Terlambat', 
                        'Alpa' => 'Alpa', 
                        'Izin' => 'Izin', 
                        'Sakit' => 'Sakit'
                    ]),
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
                        ->body('Data presensi telah di update ke sistem.')
                    ),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Hapus Data')
                    ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Berhasil')
                        ->body('Data presensi telah di hapus dari sistem.')
                    ),
                ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            // 'create' => Pages\CreateAttendance::route('/create'),
            // 'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}