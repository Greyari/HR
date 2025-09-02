<?php

namespace App\Observers;

use App\Models\Absensi;

class AbsensiObserver
{
    /**
     * Handle the Absensi "created" event.
     */
    public function created(Absensi $absensi): void
    {
        activity_log('Check-in', 'Absensi', "Karyawan {$absensi->user->nama} Melakukan Check-in");
    }

    /**
     * Handle the Absensi "updated" event.
     */
    public function updated(Absensi $absensi): void
    {
        // hanya log kalau checkout diisi
        if ($absensi->isDirty('checkout_time')) {
            activity_log('Check-out', 'Absensi', "Karyawan {$absensi->user->nama} melakukan check-out");
        }
    }

    /**
     * Handle the Absensi "deleted" event.
     */
    public function deleted(Absensi $absensi): void
    {
        // mungkin nenti bisa di tambahkan
    }
}
