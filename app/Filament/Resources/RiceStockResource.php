<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiceStockResource\Pages;
use App\Models\RiceStock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class RiceStockResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = RiceStock::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Gudang Beras';
    protected static ?string $modelLabel = 'Data Stok';
    protected static ?string $pluralModelLabel = 'Data Beras Masuk';
    protected static ?string $navigationGroup = 'Data Operasional';
    protected static ?int $navigationSort = 3;

    /**
     * 1. FILTER TAMPILAN (Daftar Data)
     * Admin OPD hanya melihat data miliknya sendiri.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['department']);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jika bukan super_admin, filter berdasarkan department_id user yang login
        if (!$user->hasRole('super_admin')) {
            $query->where('department_id', $user->department_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Input Kedatangan Beras')
                    ->description('Pastikan data diisi sesuai dengan fakta fisik di gudang.')
                    ->schema([
                        TextInput::make('tahap')
                            ->label('Tahap Masuk Beras')
                            ->placeholder('Contoh: Tahap 1')
                            ->required(),

                        DatePicker::make('tanggal_masuk')
                            ->label('Tanggal Kedatangan')
                            ->default(now())
                            ->required(),

                        TextInput::make('jumlah_karung')
                            ->label('Jumlah Karung')
                            ->numeric()
                            ->required()
                            ->live() // Aktifkan live agar Placeholder terupdate
                            ->afterStateUpdated(fn ($set, $get) => 
                                $set('total_kg', (float)$get('jumlah_karung') * (float)$get('berat_per_karung'))
                            ),

                        TextInput::make('berat_per_karung')
                            ->label('Berat Per Karung')
                            ->numeric()
                            ->default(50)
                            ->required()
                            ->live()
                            ->suffix('Kg')
                            ->afterStateUpdated(fn ($set, $get) => 
                                $set('total_kg', (float)$get('jumlah_karung') * (float)$get('berat_per_karung'))
                            ),

                        TextInput::make('keterangan')
                            ->label('Keterangan / Sumber')
                            ->columnSpanFull(),

                        Placeholder::make('total_kalkulasi')
                            ->label('Total Berat Otomatis')
                            ->content(function (Forms\Get $get) {
                                $jumlah = (float) ($get('jumlah_karung') ?? 0);
                                $berat = (float) ($get('berat_per_karung') ?? 0);
                                $total = $jumlah * $berat;
                                return "📦 " . number_format($total, 2, ',', '.') . " Kg Beras";
                            })
                            ->extraAttributes(['class' => 'text-primary-600 font-bold text-lg bg-primary-50 p-2 rounded-lg border border-primary-200']),
                        
                        // 2. FILTER PENYIMPANAN (Force Department ID)
                        // Menyimpan total_kg yang dihitung otomatis ke database
                        Hidden::make('total_kg'),

                        // Otomatisasi Department ID dari Admin yang sedang Login agar tidak tertukar
                        Hidden::make('department_id')
                            ->default(fn () => Auth::user()->department_id)
                            ->required(),

                    ])->columns(2)
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

                TextColumn::make('tanggal_masuk')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('tahap')
                    ->label('Tahap')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('department.name')
                    ->label('Bidang/Dinas')
                    ->badge()
                    ->color('gray')
                    // Hanya Super Admin yang boleh melihat asal Department di tabel
                    ->visible(fn () => $user->hasRole('super_admin')),

                TextColumn::make('total_kg')
                    ->label('Total Berat')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => number_format((float)$state, 2, ',', '.') . ' Kg')
                    ->sortable(),

                TextColumn::make('sisa_real')
                    ->label('Sisa di Gudang')
                    ->state(function ($record) {
                        $terpakai = \App\Models\RiceDistribution::where('department_id', $record->department_id)
                            ->where('tahap', $record->tahap)
                            ->sum('amount_kg');
                        return (float)$record->total_kg - (float)$terpakai;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', '.') . ' Kg')
                    ->color(fn($state) => $state <= 0 ? 'danger' : 'warning')
                    ->weight('bold'),
            ])
            ->defaultSort('tanggal_masuk', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Filter Bidang')
                    ->relationship('department', 'name')
                    // Hanya Super Admin yang boleh menggunakan filter ini
                
                    ->visible(fn () => $user->hasRole('super_admin')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->iconButton()
                ->slideOver()
                ->tooltip('Lihat Detail'),
                
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

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRiceStocks::route('/'),
            // 'create' => Pages\CreateRiceStock::route('/create'),
            // 'edit' => Pages\EditRiceStock::route('/{record}/edit'),
        ];
    }
}