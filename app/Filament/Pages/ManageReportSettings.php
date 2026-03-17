<?php

namespace App\Filament\Pages;

use App\Models\ReportSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;

class ManageReportSettings extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Laporan';
    protected static ?string $title = 'Pengaturan Header & Footer Laporan';
    protected static ?string $navigationGroup = 'Master Data';
    protected static string $view = 'filament.pages.manage-report-settings';

    public ?array $data = [];

    // --- PROTEKSI AKSES ---
    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user ?->hasRole('super_admin') ?? false;
    }

    public function mount(): void
    {
        // Langsung ambil array untuk performa
        $settings = ReportSetting::find(1);
        $this->form->fill($settings ? $settings->toArray() : []);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        Tabs\Tab::make('Header')
                            ->icon('heroicon-m-stop')
                            ->schema([
                                FileUpload::make('logo')
                                    ->image()
                                    ->label('Logo Pemda')
                                    ->directory('report-config')
                                    ->maxSize(1024) // Batasi 1MB agar PDF ringan
                                    ->imageEditor(),
                                TextInput::make('nama_pemda')->required(),
                                TextInput::make('judul_laporan')->required(),
                                TextInput::make('sub_judul'),
                                Textarea::make('alamat')->rows(3),
                            ]),
                        Tabs\Tab::make('Tanda Tangan')
                            ->icon('heroicon-m-pencil-square')
                            ->schema([
                                Toggle::make('aktifkan_ttd_digital')
                                    ->label('Gunakan TTD Digital')
                                    ->live()
                                    ->default(false),

                                Grid::make(2)->schema([
                                    Section::make('Pihak Pertama (Kiri)')
                                        ->columnSpan(1)
                                        ->schema([
                                            TextInput::make('jabatan_kiri'),
                                            TextInput::make('nama_kiri'),
                                            TextInput::make('nip_kiri')->label('NIP'),
                                            FileUpload::make('ttd_kiri')
                                                ->label('Scan TTD Kiri')
                                                ->image()
                                                ->imageEditor() // Mengizinkan crop/resize manual yang aman
                                                ->directory('ttd')
                                                ->acceptedFileTypes(['image/jpeg', 'image/png']) // Batasi jenis file
                                                ->visibility('public')
                                                ->imageResizeMode('cover')
                                                ->imagePreviewHeight('150')
                                                ->imageResizeTargetWidth('400') // Mengecilkan ke 400px otomatis
                                                ->imageResizeTargetHeight('200')
                                                ->loadingIndicatorPosition('left')
                                                ->removeUploadedFileButtonPosition('right')
                                              
                                                ->extraAttributes(['loading' => 'lazy']) // Memberi instruksi browser untuk muat santai
                                                ->visible(fn ($get) => $get('aktifkan_ttd_digital')),
                                        ]),
                                    Section::make('Pihak Kedua (Kanan)')
                                        ->columnSpan(1)
                                        ->schema([
                                            TextInput::make('jabatan_kanan'),
                                            TextInput::make('nama_kanan'),
                                            TextInput::make('nip_kanan')->label('NIP'),
                                            FileUpload::make('ttd_kanan')
                                                ->label('Scan TTD Kanan')
                                                ->image()
                                                ->imageEditor() // Mengizinkan crop/resize manual yang aman
                                                ->directory('ttd')
                                                ->acceptedFileTypes(['image/jpeg', 'image/png']) // Batasi jenis file
                                                ->visibility('public')
                                                ->imageResizeMode('cover')
                                                ->imagePreviewHeight('150')
                                                ->imageResizeTargetWidth('400') // Mengecilkan ke 400px otomatis agar PDF ringan
                                                ->imageResizeTargetHeight('200')
                                                ->loadingIndicatorPosition('left')
                                                ->removeUploadedFileButtonPosition('right')
                                               
                                                ->extraAttributes(['loading' => 'lazy']) // Memberi instruksi browser untuk muat santai
                                                ->visible(fn ($get) => $get('aktifkan_ttd_digital')),
                                        ]),
                                ]),
                            ]),
                    ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        try {
            $state = $this->form->getState();
            
            ReportSetting::updateOrCreate(['id' => 1], $state);

            Notification::make()
                ->title('Pengaturan berhasil diperbarui')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal menyimpan')
                ->danger()
                ->body('Terjadi kesalahan sistem.')
                ->send();
        }
    }
}