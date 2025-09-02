<?php

namespace App\Observers;

use App\Models\Lembur;
use Illuminate\Support\Facades\Auth;

class LemburObserver
{
    /**
     * Handle the Lembur "created" event.
     */
    public function created(Lembur $lembur): void
    {
        activity_log('Mengajukan', 'Lembur', "Karyawan {$lembur->user->nama} melakukan pengajuan lembur");
    }

    /**
     * Handle the Lembur "updated" event.
     */
    public function updated(Lembur $lembur): void
    {
        $actor = Auth::user();
        $namaActor = $actor->nama;
        $peranActor = $actor->peran->nama_peran;
        $pemohon = $lembur->user->nama;

        if ($lembur->isDirty('status')) {
            $statusBaru = $lembur->status;

            if ($statusBaru === 'Proses') {
                activity_log(
                    "{$peranActor} Menyetujui",
                    'Lembur',
                    "{$namaActor} menyetujui lembur milik {$pemohon}"
                );
            }

            if ($statusBaru === 'Disetujui') {
                activity_log(
                    "{$peranActor} Menyetujui",
                    'Lembur',
                    "{$namaActor} memberikan persetujuan final pada lembur milik {$pemohon}"
                );
            }

            if ($statusBaru === 'Ditolak') {
                activity_log(
                    "{$peranActor} Menolak",
                    'Lembur',
                    "{$namaActor} menolak pengajuan lembur milik {$pemohon}"
                );
            }
        }
    }

    /**
     * Handle the Lembur "deleted" event.
     */
    public function deleted(Lembur $lembur): void
    {
        $nama = $lembur->user->nama;
        activity_log('Menghapus', 'Lembur', "Menghapus data lembur {$nama}");
    }

    /**
     * Handle the Lembur "restored" event.
     */
    public function restored(Lembur $lembur): void
    {
        //
    }

    /**
     * Handle the Lembur "force deleted" event.
     */
    public function forceDeleted(Lembur $lembur): void
    {
        //
    }
}
