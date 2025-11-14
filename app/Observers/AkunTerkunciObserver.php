<?php

namespace App\Observers;

use App\Models\User;

class AkunTerkunciObserver
{
    public function updated(User $user)
    {
        if (app()->runningInConsole()) return;

        // Khusus jika kolom "terkunci" berubah
        if ($user->isDirty('terkunci')) {

            // Ambil nilai lama dan baru
            $original = $user->getOriginal('terkunci');
            $new      = $user->terkunci;

            $nama = $user->getOriginal()['nama'] ?? $user->nama;

            // Jika akun dibuka
            if ($original == true && $new == false) {
                activity_log(
                    'Buka Akun',
                    'Akun Terkunci',
                    "Admin membuka kunci akun {$nama}"
                );
            }

            // // Jika akun dikunci (misalnya karena gagal login)
            // if ($original == false && $new == true) {
            //     activity_log(
            //         'Kunci Akun',
            //         'Akun Terkunci',
            //         "Akun {$nama} dikunci karena gagal login"
            //     );
            // }
        }
    }
}
