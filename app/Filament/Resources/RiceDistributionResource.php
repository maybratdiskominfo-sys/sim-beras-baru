<?php

namespace App\Filament\Resources;

use App\Models\Employee;
use App\Models\RiceStock;
use App\Models\RiceDistribution;
use App\Filament\Resources\RiceDistributionResource\Pages;
use App\Filament\Widgets\RiceAnalysisOverview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{Select, TextInput, DateTimePicker, Section, Placeholder};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Illuminate\Support\Facades\{Gate, Auth, Filament};
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Filters\SelectFilter;

class RiceDistributionResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = RiceDistribution::class;

    protected static ?string $modelLabel       = 'Pengambilan';
    protected static ?string $pluralModelLabel = 'Pengambilan Beras';
    protected static ?string $navigationIcon   = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel  = 'Pengambilan Beras';
    protected static ?string $navigationGroup  = 'Data Operasional';
    protected static ?int $navigationSort      = 2;

    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['employee', 'department']);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jika super_admin lihat semua, jika bukan (Admin OPD) filter by department_id
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query->where('department_id', $user->department_id);
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view', 
            'view_any', 
            'create', 
            'update', 
            'delete', 
            'delete_any'];
    }




    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Pencatatan Tahapan Pengambilan')
                    ->description('Sistem akan memvalidasi jatah pegawai dan sisa stok gudang secara otomatis.')
                    ->schema([
                        // 1. Nama Pegawai dikunci saat edit agar tidak terjadi manipulasi data antar pegawai
                Select::make('employee_id')
                    ->label('Nama Pegawai')
                    ->options(function () {
                        $query = Employee::where('is_active', true);
                        if (!Gate::allows('super_admin')) {
                            $query->where('department_id', Auth::user()->department_id);
                        }
                        return $query->pluck('nama_lengkap', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->disabled(fn (string $operation): bool => $operation === 'edit') // Kunci saat edit
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => 
                        $set('receiver_name', Employee::find($state)?->nama_lengkap)
                    ),

                // 2. Tahap Pengambilan dikunci saat edit agar stok gudang tidak berantakan
                Select::make('tahap')
                    ->label('Tahap Pengambilan')
                    ->options(fn () => 
                        RiceStock::where('department_id', Auth::user()->department_id)
                            ->whereNotNull('tahap')
                            ->distinct()
                            ->pluck('tahap', 'tahap')
                    )
                    ->required()
                    ->live()
                    ->native(false)
                    ->disabled(fn (string $operation): bool => $operation === 'edit') // Kunci saat edit
                            ->label('Tahap Pengambilan')
                            ->options(fn () => 
                                RiceStock::where('department_id', Auth::user()->department_id)
                                    ->whereNotNull('tahap')
                                    ->distinct()
                                    ->pluck('tahap', 'tahap')
                            )
                            ->required()
                            ->live()
                            ->native(false),

                        Placeholder::make('info_sisa_tahap')
                            ->label('Status Jatah Pegawai')
                            ->content(function ($get) {
                                $empId = $get('employee_id');
                                $tahap = $get('tahap');
                                if (!$empId || !$tahap) return "Silahkan pilih pegawai dan tahap.";

                                $emp = Employee::find($empId);
                                $sudah = RiceDistribution::where('employee_id', $empId)
                                    ->where('tahap', $tahap)
                                    ->where('year', now()->year)
                                    ->sum('amount_kg');

                                $sisa = ($emp->jatah_kg ?? 0) - $sudah;
                                return $sisa <= 0 
                                    ? "❌ Jatah HABIS (Sudah ambil {$sudah} Kg)" 
                                    : "✅ Tersedia: {$sisa} Kg (Total Jatah: {$emp->jatah_kg} Kg)";
                            })
                            ->extraAttributes(['class' => 'p-2 bg-gray-50 rounded-lg border border-dashed border-gray-300']),

                        TextInput::make('amount_kg')
                            ->label('Jumlah Disalurkan (Kg)')
                            ->numeric()
                            ->required()
                            ->live()
                            ->rules([
                                fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $tahap = $get('tahap');
                                    $deptId = Auth::user()->department_id;
                                    
                                    // 1. Validasi Stok Gudang
                                    $stok = RiceStock::where('department_id', $deptId)->where('tahap', $tahap)->sum('total_kg');
                                    $keluar = RiceDistribution::where('department_id', $deptId)->where('tahap', $tahap)->sum('amount_kg');
                                    $sisaStok = $stok - $keluar;

                                    if ($value > $sisaStok) {
                                        $fail("Stok gudang tidak cukup! Sisa: {$sisaStok} Kg.");
                                    }

                                    // 2. Validasi Jatah Pegawai (Opsional jika ingin double check di sini)
                                    $emp = Employee::find($get('employee_id'));
                                    if($emp && $value > $emp->jatah_kg) {
                                        $fail("Input ({$value}Kg) melebihi jatah pegawai ({$emp->jatah_kg}Kg).");
                                    }
                                },
                            ]),

                        TextInput::make('receiver_name')
                            ->label('Nama Penerima')
                            ->required(),

                        DateTimePicker::make('taken_at')
                            ->label('Waktu Pengambilan')
                            ->default(now())
                            ->required(),

                        Forms\Components\Hidden::make('month')->default(now()->month),
                        Forms\Components\Hidden::make('year')->default(now()->year),
                        // Department ID diambil otomatis dari user yang login
                        Forms\Components\Hidden::make('department_id')->default(fn() => Auth::user()->department_id),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')->label('No.')->rowIndex(),
                
                TextColumn::make('employee.nama_lengkap')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => "NIP: " . $record->employee->nip),

                TextColumn::make('receiver_name')
                    ->label('Penerima')
                    ->searchable()
                    ->sortable(),
                    // ->description('Keluarga'),

                TextColumn::make('tahap')
                    ->label('Tahap')
                    ->badge(),

                TextColumn::make('amount_kg')
                    ->label('Jumlah')
                    ->suffix(' Kg')
                    ->weight('bold')
                    ->color(function ($record) {
                        $jatah = (float) $record->employee->jatah_kg;
                        return (float)$record->amount_kg < $jatah ? 'warning' : 'success';
                    }),

                TextColumn::make('taken_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tahap')
                    ->options(fn() => RiceStock::where('department_id', Auth::user()->department_id)->pluck('tahap', 'tahap')),
            ])

            ->actions([
                Tables\Actions\ViewAction::make()->iconButton()->tooltip('Lihat Detail'),
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->modalHeading('Ubah Data Pegawai')
                    ->tooltip('Ubah Data'),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Hapus Data'),
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
            'index' => Pages\ListRiceDistributions::route('/'),
            // 'create' => Pages\CreateRiceDistribution::route('/create'),
            // 'edit' => Pages\EditRiceDistribution::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            RiceAnalysisOverview::class, // Gunakan ::class, bukan string
        ];
    }
}