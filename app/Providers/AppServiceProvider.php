<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\ReportSetting;
use App\Models\User;
use App\Observers\EmployeeObserver;
use App\Observers\ReportSettingObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use BezhanSalleh\FilamentShield\Resources\RoleResource;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void { }

    public function boot(): void
    {
        // PERINTAH WAJIB: Supaya Role tidak mencari kolom 'department_id'
        RoleResource::isScopedToTenant(false); 
        // \App\Models\Employee::observe(\App\Observers\EmployeeObserver::class);
        User::observe(UserObserver::class);
        Employee::observe(EmployeeObserver::class);
        ReportSetting::observe(ReportSettingObserver::class);
    }

}