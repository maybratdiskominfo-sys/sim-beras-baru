<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Berita & Postingan';

    /**
     * Menghubungkan Resource ini dengan sistem Tenant (Department).
     * Ini akan otomatis memfilter data berdasarkan department_id yang aktif.
     */
    protected static ?string $tenantRelationshipName = 'posts';

    /**
     * CATATAN PROFESIONAL: 
     * Baris 'tenantOwnershipRelationshipName' dihapus agar semua staf di 
     * departemen yang sama dapat melihat postingan rekan sejawatnya.
     */

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konten Berita')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Berita')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('URL Friendly (Slug)')
                            ->required()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\RichEditor::make('content')
                            ->label('Isi Berita')
                            ->required()
                            ->helperText('Gunakan tombol <> untuk memasukkan embed video atau media sosial.')
                            ->columnSpanFull(),
                    ])->columnSpan(2),

                Forms\Components\Section::make('Meta Data')
                    ->schema([
                        Forms\Components\FileUpload::make('thumbnail')
                            ->label('Foto Sampul')
                            ->image()
                            ->directory('berita-thumbnails'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Tayangkan',
                            ])
                            ->default('published')
                            ->required(),

                        Forms\Components\TextInput::make('category')
                            ->label('Kategori (Bidang)')
                            ->default(fn () => Auth::user()->employee->bidang ?? 'Umum')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => Auth::id()),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Sampul')
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Berita')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category')
                    ->label('Bidang')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Penulis')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->options([
                        'heroicon-o-check-circle' => 'published',
                        'heroicon-o-clock' => 'draft',
                    ])
                    ->colors([
                        'success' => 'published',
                        'warning' => 'draft',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terbit Pada')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),
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

    public static function getEloquentQuery(): Builder
    {
        /**
         * Menggunakan query standar Filament Tenancy.
         * SuperAdmin biasanya bypass filter ini melalui Policy/Shield.
         */
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}