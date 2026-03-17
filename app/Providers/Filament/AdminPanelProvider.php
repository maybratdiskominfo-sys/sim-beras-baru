<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AttendanceWidget;
use Filament\Navigation\MenuItem;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\RekapTahapan;
use App\Filament\Pages\ReportLaporanPenyaluran;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->sidebarWidth('18rem')
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('8rem')
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Indigo,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->brandName('SIM BERAS DISKOMINFO KAB. MAYBRAT')

            // --- KONFIGURASI TENANCY ---
            // Baris yang menyebabkan error sudah dihapus.
            ->tenant(\App\Models\Department::class, slugAttribute: 'slug')
            ->tenantMenu(true)

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')

            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return ($user->name ?? 'User');
                    })
                    ->icon('heroicon-m-user-circle'),
                'logout' => MenuItem::make()
                    ->label('Keluar dari Sistem'),
            ])

            ->widgets([
                AttendanceWidget::class,
            ])

            ->pages([
                Dashboard::class,
                ReportLaporanPenyaluran::class,
                RekapTahapan::class,
            ])

            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])

            ->middleware([
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\Session\Middleware\AuthenticateSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Filament\Http\Middleware\DisableBladeIconComponents::class,
                \Filament\Http\Middleware\DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([\Filament\Http\Middleware\Authenticate::class])
            ->profile(EditProfile::class);
    }
}