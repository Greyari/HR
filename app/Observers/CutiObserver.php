<?php

namespace App\Observers;

use App\Models\Cuti;
use Illuminate\Support\Facades\Auth;

class CutiObserver
{
    /**
     * Handle the Cuti "created" event.
     */
    public function created(Cuti $cuti): void
    {
        activity_log('Mengajukan', 'Cuti', "Karyawan {$cuti->user->nama} melakukan pengajuan cuti");

    }

    /**
     * Handle the Cuti "updated" event.
     */
    public function updated(Cuti $cuti): void
    {
        $actor = Auth::user();
        $namaActor = $actor->nama;
        $peranActor = $actor->peran->nama_peran;
        $pemohon = $cuti->user->nama;

        if ($cuti->isDirty('status')) {
            $statusBaru = $cuti->status;

            if ($statusBaru === 'Proses') {
                activity_log(
                    "{$peranActor} Menyetujui",
                    'Cuti',
                    "{$namaActor} menyetujui cuti milik {$pemohon}"
                );
            }

            if ($statusBaru === 'Disetujui') {
                activity_log(
                    "{$peranActor} Menyetujui",
                    'Cuti',
                    "{$namaActor} memberikan persetujuan final pada cuti milik {$pemohon}"
                );
            }

            if ($statusBaru === 'Ditolak') {
                activity_log(
                    "{$peranActor} Menolak",
                    'Cuti',
                    "{$namaActor} menolak pengajuan cuti milik {$pemohon}"
                );
            }
        }
    }

    // /**
    //  * Handle the Cuti "deleted" event.
    //  */
    // public function deleted(Cuti $cuti): void
    // {
    //     $original = $cuti->getOriginal();
    //     $nama = $original['nama'];

    //     activity_log('Menghapus', 'Cuti', "Menghapus data cuti {$nama}");
    // }

    /**
     * Handle the Cuti "restored" event.
     */
    public function restored(Cuti $cuti): void
    {
        //
    }

    /**
     * Handle the Cuti "force deleted" event.
     */
    public function forceDeleted(Cuti $cuti): void
    {
        //
    }
}
