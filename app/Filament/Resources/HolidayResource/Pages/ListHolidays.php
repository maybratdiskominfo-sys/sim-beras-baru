<?php

namespace App\Filament\Resources\HolidayResource\Pages;

use App\Filament\Resources\HolidayResource;
use App\Models\Holiday;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class ListHolidays extends ListRecords
{
    protected static string $resource = HolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Actions\Action::make('syncApi')
                ->label('Ambil Libur Nasional')
                ->icon('heroicon-m-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Sinkronisasi Data')
                ->modalDescription('Mengambil data libur nasional tahun ini dari API. Pastikan komputer terhubung ke internet.')
                ->action(function () {
                    $year = now()->year;
                    $tenant = Filament::getTenant();

                    if (!$tenant) {
                        Notification::make()->title('Tenant tidak ditemukan')->danger()->send();
                        return;
                    }

                    try {
                        // Menggunakan API alternatif yang lebih umum
                        $response = Http::timeout(15)->get("https://api-harilibur.vercel.app/api?year={$year}");

                        if ($response->successful()) {
                            $holidays = $response->json();
                            $addedCount = 0;

                            foreach ($holidays as $data) {
                                // API ini menggunakan key 'holiday_date' dan 'holiday_name'
                                // Kita gunakan null coalescing (??) agar tetap kompatibel jika key berubah
                                $rawDate = $data['holiday_date'] ?? $data['calendar_date'] ?? null;
                                $description = $data['holiday_name'] ?? $data['description'] ?? 'Libur Nasional';

                                if ($rawDate) {
                                    $formattedDate = Carbon::parse($rawDate)->format('Y-m-d');

                                    $holiday = Holiday::updateOrCreate(
                                        [
                                            'tenant_id' => $tenant->id,
                                            'date'      => $formattedDate,
                                        ],
                                        [
                                            'description' => $description,
                                        ]
                                    );

                                    if ($holiday->wasRecentlyCreated) {
                                        $addedCount++;
                                    }
                                }
                            }

                            Notification::make()
                                ->title($addedCount > 0 ? 'Data Berhasil Ditarik' : 'Data Sudah Up-to-date')
                                ->body("Berhasil memproses " . count($holidays) . " data (Baru: {$addedCount}).")
                                ->success()
                                ->send();
                            
                            // Refresh halaman agar data muncul
                            return redirect(static::$resource::getUrl('index'));
                            
                        } else {
                            throw new \Exception("Server API memberikan respon error: " . $response->status());
                        }

                    } catch (\Exception $e) {
                        // Menangani cURL error 6 atau masalah koneksi lainnya
                        Notification::make()
                            ->title('Gagal Sinkronisasi')
                            ->body('Koneksi internet bermasalah atau API sedang down. Pesan: ' . $e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }
}