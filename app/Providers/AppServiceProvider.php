<?php

namespace App\Providers;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Departemen;
use App\Models\Gaji;
use App\Models\Jabatan;
use App\Models\Kantor;
use App\Models\Lembur;
use App\Models\Pengingat;
use App\Models\PotonganGaji;
use App\Models\Tugas;
use App\Models\User;
use App\Observers\AbsensiObserver;
use App\Observers\CutiObserver;
use App\Observers\DepartemenObserver;
use App\Observers\GajiObserver;
use App\Observers\JabatanObserver;
use App\Observers\KantorObserver;
use App\Observers\KaryawanObserver;
use App\Observers\LemburObserver;
use App\Observers\PengingatObservers;
use App\Observers\PotonganGajiObserver;
use App\Observers\TugasObserver;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');
        User::observe(KaryawanObserver::class);
        Departemen::observe(DepartemenObserver::class);
        Jabatan::observe(JabatanObserver::class);
        PotonganGaji::observe(PotonganGajiObserver::class);
        Kantor::observe(KantorObserver::class);
        Gaji::observe(GajiObserver::class);
        Absensi::observe(AbsensiObserver::class);
        Tugas::observe(TugasObserver::class);
        Lembur::observe(LemburObserver::class);
        Cuti::observe(CutiObserver::class);
        Pengingat::observe(PengingatObservers::class);
    }
}
