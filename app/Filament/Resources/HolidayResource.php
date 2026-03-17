<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HolidayResource\Pages;
use App\Models\Holiday;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Pengaturan Jadwal';

    protected static ?string $label = 'Hari Libur';

    /** * KONFIGURASI MULTI-TENANCY
     */
    protected static bool $isScopedToTenant = true;
    
    // Harus sama dengan nama function di model Department.php
    protected static ?string $tenantRelationshipName = 'holidays';
    
    // Harus sama dengan nama function di model Holiday.php
    protected static ?string $tenantOwnershipRelationshipName = 'department';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Hari Libur')
                    ->description('Tentukan tanggal merah atau libur khusus instansi.')
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                $tenant = Filament::getTenant();
                                return $rule->where('tenant_id', $tenant ? $tenant->id : null);
                            }),

                        Forms\Components\TextInput::make('description')
                            ->label('Keterangan Libur')
                            ->placeholder('Contoh: Libur Nasional Idul Fitri')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('l, d F Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\Filter::make('tahun_ini')
                    ->label('Tahun Ini')
                    ->query(fn (Builder $query): Builder => $query->whereYear('date', now()->year)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListHolidays::route('/'),
            'create' => Pages\CreateHoliday::route('/create'),
            'edit' => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}