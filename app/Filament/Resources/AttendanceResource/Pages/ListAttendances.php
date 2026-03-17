<?php
namespace App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    /**
     * MENGATUR TOMBOL DI ATAS TABEL
     * Tombol "Input Manual" hanya muncul untuk Admin.
     */
    protected function getHeaderActions(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user && $user->hasAnyRole(['super_admin', 'admin_opd'])) {
            return [
                Actions\CreateAction::make()
            ->label('Tambah Data Presensi')
                ->modalHeading('Buat data Presensi baru')
                ->modalWidth('2xl')
                ->slideOver() // Opsional: gunakan slideOver jika ingin modal muncul dari samping (lebih elegan)
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Berhasil')
                        ->body('Data Presensi telah dicatat ke sistem.')
                )
                // Memastikan tabel di belakang langsung ter-update
                ->after(function () {
                    $this->dispatch('refreshTable'); 
                }),
            ];
        }

        return [];
    }

    /**
     * LOGIKA FILTERING DATA (TENANCY & ROLE)
     * Menggunakan getTableQuery untuk memastikan data terisolasi dengan aman.
     */
    protected function getTableQuery(): Builder
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Memanggil query dasar dari Resource
        $query = static::getResource()::getEloquentQuery();

        // 1. Jika Super Admin, biarkan melihat semua data
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // 2. Jika Admin OPD, filter berdasarkan department_id milik Admin tersebut
        if ($user->hasRole('admin_opd')) {
            return $query->where('department_id', $user->department_id);
        }

        // 3. Jika User Biasa (Pegawai), WAJIB hanya melihat datanya sendiri
        $employeeId = $user->employee?->id;

        // Jika user tidak punya relasi employee, cegah melihat data apapun (return empty)
        if (!$employeeId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('employee_id', $employeeId);
    }

    /**
     * MENAMBAHKAN TAB FILTER CEPAT
     * Tab "Luar Radius" hanya muncul untuk Admin.
     */
    public function getTabs(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $tabs = [
            'semua' => \Filament\Resources\Components\Tab::make('Semua Riwayat'),
            'hadir' => \Filament\Resources\Components\Tab::make('Hadir')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Hadir')),
            'izin_sakit' => \Filament\Resources\Components\Tab::make('Izin/Sakit')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['Izin', 'Sakit', 'Dinas Luar'])),
        ];

        // Tab khusus Admin untuk memantau pelanggaran radius
        if ($user && $user->hasAnyRole(['super_admin', 'admin_opd'])) {
            $tabs['luar_radius'] = \Filament\Resources\Components\Tab::make('Luar Radius')
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereHas('employee.department', function ($q) {
                        $q->whereRaw('attendances.distance_from_office > departments.radius_meter');
                    });
                })
                ->badgeColor('danger');
        }

        return $tabs;
    }
}