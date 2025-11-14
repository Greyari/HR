<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class KaryawanObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if (app()->runningInConsole()) {
            return; // skip log saat seeding/migrate
        }

        activity_log('Menambahkan', 'Karyawan', "Menambahkan data karyawan {$user->nama}");
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if (app()->runningInConsole()) return;

        $changes = $user->getDirty();
        $original = $user->getOriginal();

        $ignore = [
            'updated_at',
            'created_at',
            'remember_token',
            'coba_login',
            'last_login',
            'device_token',
            'terkunci',
        ];

        // kalau semua perubahan ada di daftar ignore → skip log
        if (! collect($changes)->except($ignore)->isNotEmpty()) {
            return;
        }

        $detailChanges = [];
        foreach ($changes as $field => $newValue) {
            if (in_array($field, $ignore)) continue;
            $oldValue = $original[$field] ?? null;
            $detailChanges[] = "{$field}: '{$oldValue}' → '{$newValue}'";
        }

        if ($detailChanges) {
            $description = "Perubahan: " . implode(', ', $detailChanges);

            if (Auth::check() && Auth::id() === $user->id) {
                // user update dirinya sendiri → Profile
                activity_log(
                    'Mengubah',
                    'Profile',
                    "Perubahan data profile {$user->nama}. {$description}"
                );
            } else {
                // admin update user lain → Karyawan
                activity_log(
                    'Mengubah',
                    'Karyawan',
                    "Memperbarui data karyawan {$user->nama}. {$description}"
                );
            }
        }
    }

    /**
     * Event sebelum user dihapus.
     * Di sini kita hapus semua log milik user itu supaya tidak error null.
     */
    public function deleting(User $user): void
    {
        Activity::where('user_id', $user->id)->delete();
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        if (app()->runningInConsole()) {
            return; // skip log saat seeding/migrate
        }

        $nama = $user->getOriginal()['nama'] ?? $user->nama;

        activity_log('Menghapus', 'Karyawan', "Menghapus akun karyawan {$nama}");
    }
}
